<?php

namespace App\Repositories\API;

use App\Enums\RoleEnum;
use App\Enums\UserTypeEnum;
use App\Exceptions\ExceptionHandler;
use App\Helpers\Helpers;
use App\Http\Resources\ServicemanResource;
use App\Models\Address;
use App\Models\User;
use App\Models\UserDocument;
use Exception;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Prettus\Repository\Eloquent\BaseRepository;
use Spatie\Permission\Models\Role;

class ServicemanRepository extends BaseRepository
{
    protected $fieldSearchable = [
        'name' => 'like',
    ];

    protected $role;

    protected $address;

    public function model()
    {
        $this->address = new Address();
        $this->role = new Role();

        return User::class;
    }

    public function getServicemans()
    {
        try {

            return $this->model->role('serviceman')->with(['addresses', 'servicemanreviews']);

        } catch (Exception $e) {

            throw new ExceptionHandler($e->getMessage(), $e->getCode());
        }
    }

    public function show($id)
    {
        $serviceman = $this->model->findOrFail($id);
        return response()->json([
            'success' => true,
            'data' =>  new ServicemanResource($serviceman),
        ]);

    }

    public function isProviderCanCreate()
    {
        if (Helpers::isUserLogin()) {
            $isAllowed = true;
            $roleName = Helpers::getCurrentRoleName();
            if ($roleName == RoleEnum::PROVIDER) {
                $isAllowed = false;
                $provider = Auth::user();
                $maxItems = $provider?->servicemans()->count();
                if (Helpers::isModuleEnable('Subscription')) {
                    if (function_exists('isPlanAllowed')) {
                        $isAllowed = isPlanAllowed('allowed_max_servicemen', $maxItems, $provider?->id);
                    }
                }

                if (! $isAllowed) {
                    $settings = Helpers::getSettings();
                    $max_serviceman = $settings['default_creation_limits']['allowed_max_servicemen'];
                    if ($max_serviceman > $maxItems) {
                        $isAllowed = true;
                    }
                }
            }

            return $isAllowed;
        }
    }

    public function store($request)
    {
        DB::beginTransaction();
        try {
            if (Helpers::isUserLogin()) {
               $roleName = Helpers::getCurrentRoleName();
               $provider = Auth::user();
               if ($roleName == RoleEnum::PROVIDER) {
                    if ($provider->type == UserTypeEnum::COMPANY && !$provider->company) {
                        throw new Exception(__('static.provider.please_fill_company_details_serviceman'), 422);
                    }  
                    
                    $hasDocuments = $provider->UserDocuments()->exists();
                    if (!$hasDocuments) {
                        throw new Exception(__('static.provider.please_upload_documents_first_serviceman'), 422);
                    }
                }
            }

            if ($this->isProviderCanCreate()) {
                $serviceman = $this->model->create([
                    'provider_id' => $request->provider_id,
                    'name' => $request->name,
                    'email' => $request->email,
                    'code' => $request->code,
                    'phone' => $request->phone,
                    'password' => Hash::make($request->password),
                    'experience_interval' => $request->experience_interval,
                    'experience_duration' => $request->experience_duration,
                    'description' => $request->description,
                ]);

                $role = $this->role->where('name', RoleEnum::SERVICEMAN)->first();
                $serviceman->assignRole($role);

                if (! empty($request->known_languages)) {
                    $serviceman->knownLanguages()->attach($request->known_languages);
                    $serviceman->knownLanguages;
                }

                if ($request->hasFile('image') && $request->file('image')->isValid()) {
                    $serviceman->addMediaFromRequest('image')->toMediaCollection('image');
                }

                if ($request->documents_images) {
                    $servicemanDocument = UserDocument::create([
                        'user_id' => $serviceman->id,
                        'document_id' => $request->document_id,
                        'status' => 'pending',
                        'identity_no' => $request->identity_no,
                    ]);
                    $images = $request->file('documents_images');
                    foreach ($images as $image) {
                        $servicemanDocument->addMedia($image)->toMediaCollection('document_images');
                    }
                    $servicemanDocument->media;
                }

                $address = $this->address->create([
                    'user_id' => $serviceman->id,
                    'latitude' => $request->latitude,
                    'longitude' => $request->longitude,
                    'area' => $request->area,
                    'postal_code' => $request->postal_code,
                    'country_id' => $request->country_id,
                    'state_id' => $request->state_id,
                    'city' => $request->city,
                    'address' => $request->address,
                    'type' => $request->type,
                    'status' => $request->status,
                    'availability_radius' => $request->availability_radius ?? null,
                    'is_primary' => 1,
                ]);

                DB::commit();

                return response()->json([
                    'success' => true,
                    'message' => __('static.serviceman.store')
                ]);
            }

            throw new Exception(__('static.not_allow_for_creation'), 400);
        } catch (Exception $e) {
            throw new ExceptionHandler($e->getMessage(), $e->getCode());
        }
    }

    public function update($request, $id)
    {
        DB::beginTransaction();
        try {
            $serviceman = $this->model->findOrFail($id);
            $serviceman->update($request->all());

            if (! empty($request->known_languages)) {
                $serviceman->knownLanguages()->sync($request->known_languages);
                $serviceman->knownLanguages;
            }

            if ($request->hasFile('image') && $request->file('image')->isValid()) {
                $serviceman->clearMediaCollection('image');
                $serviceman->addMediaFromRequest('image')->toMediaCollection('image');
            }

            $address = Address::where('user_id', $serviceman->id)
                        ->where('id', $request->address_id)
                        ->first();
            if ($address) {
                $address->update([
                    'type' => $request->type,
                    'latitude' => $request->latitude,
                    'longitude' => $request->longitude,
                    'area' => $request->area,
                    'postal_code' => $request->postal_code,
                    'country_id' => $request->country_id,
                    'state_id' => $request->state_id,
                    'city' => $request->city,
                    'address' => $request->address,
                    'status' => $request->status,
                    'availability_radius' => $request->availability_radius,
                ]);
            } else {
                throw new Exception(__('static.serviceman.invalid_address_id'), 400);
            }
            DB::commit();

            return response()->json([
                'success' => true,
                'message' => __('static.serviceman.updated')
            ]);
        } catch (Exception $e) {
            throw new ExceptionHandler($e->getMessage(), $e->getCode());
        }
    }

    public function destroy($serviceman)
    {
        $serviceman->delete();
        return response()->json([
            'success' => true,
            'message' => __('static.serviceman.destroy'),
        ]);
    }
}
