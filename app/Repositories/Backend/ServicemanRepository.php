<?php

namespace App\Repositories\Backend;

use App\Exports\ServicemanExport;
use App\Imports\ServicemanImport;
use Exception;
use App\Enums\RoleEnum;
use App\Helpers\Helpers;
use App\Models\Address;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Maatwebsite\Excel\Facades\Excel;
use Prettus\Repository\Eloquent\BaseRepository;
use Spatie\Permission\Models\Role;

class ServicemanRepository extends BaseRepository
{
    protected $role;

    protected $address;

    public function model()
    {
        $this->address = new Address();
        $this->role = new Role();
        return User::class;
    }

    public function show($id)
    {
        try {
            return $this->model->with('permissions')->findOrFail($id);
        } catch (Exception $e) {

            return back()->with('error', $e->getMessage());
        }
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

            if ($this->isProviderCanCreate()) {
                $serviceman = $this->model->create([
                    'provider_id' => $request->provider_id,
                    'name' => $request->name,
                    'email' => $request->email,
                    'code' => $request->code,
                    'phone' => $request->phone,
                    // 'is_featured' => $request->is_featured,
                    'status' => $request->status,
                    'password' => Hash::make($request->password),
                    'experience_interval' => $request->experience_interval,
                    'experience_duration' => $request->experience_duration,
                    'description' => $request->description,
                ]);

                if ($request->hasFile('image') && $request->file('image')->isValid()) {
                    $serviceman->addMediaFromRequest('image')->toMediaCollection('image');
                }

                $role = $this->role->where('name', RoleEnum::SERVICEMAN)->first();
                if ($request->role) {
                    $role = $this->role->findOrFail($request->role);
                }

                $serviceman->assignRole($role);
                if (isset($request->known_languages)) {
                    $serviceman->knownLanguages()->attach($request->known_languages);
                    $serviceman->knownLanguages;
                }

                $address = $this->address->create([
                    'user_id' => $serviceman->id,
                    'type' => $request->address_type,
                    'alternative_name' => $request->alternative_name,
                    'code' => $request->alternative_code,
                    'alternative_phone' => $request->alternative_phone,
                    'area' => $request->area,
                    'postal_code' => $request->postal_code,
                    'country_id' => $request->country_id,
                    'street_address' => $request->street_address,
                    'state_id' => $request->state_id,
                    'city' => $request->city,
                    'address' => $request->address,
                    'is_primary' => true,
                ]);

                DB::commit();

                return redirect()->route('backend.serviceman.index')->with('message', 'Serviceman Created Successfully.');

            }

            throw new Exception(__('static.not_allow_for_creation'), 400);
        } catch (Exception $e) {

            DB::rollback();

            return back()->with('error', $e->getMessage());
        }
    }

    public function update($request, $id)
    {
        DB::beginTransaction();
        try {
            $serviceman = $this->model->findOrFail($id);
            if ($serviceman->system_reserve) {
                return redirect()->route('backend.user.index')->with('error', 'This User Cannot be Update. It is System reserved.');
            }
            $serviceman->update($request->except(['_token', '_method', 'submit']));

            if ($request->hasFile('image') && $request->file('image')->isValid()) {
                $serviceman->clearMediaCollection('image');
                $serviceman->addMediaFromRequest('image')->toMediaCollection('image');
            }
            $role = $this->role->where('name', RoleEnum::SERVICEMAN)->first();
            $serviceman->syncRoles($role);

            if (isset($request['known_languages'])) {
                $serviceman->knownLanguages()->sync($request['known_languages']);
            }

            $address = $this->address->where('user_id', $serviceman->id)->where('is_primary', true)->first();
            $address->update([
                'user_id' => $serviceman->id,
                'type' => $request['address_type'],
                'alternative_name' => $request['alternative_name'],
                'code' => $request['alternative_code'],
                'alternative_phone' => $request['alternative_phone'],
                'area' => $request['area'],
                'street_address' => $request['street_address'],
                'postal_code' => $request['postal_code'],
                'country_id' => $request['country_id'],
                'state_id' => $request['state_id'],
                'city' => $request['city'],
                'address' => $request['address'],
            ]);

            DB::commit();

            return redirect()->route('backend.serviceman.index')->with('message', 'Serviceman Updated Successfully.');
        } catch (Exception $e) {

            DB::rollback();

            return back()->with('error', $e->getMessage());
        }
    }

    public function destroy($id)
    {
        DB::beginTransaction();
        try {

            $serviceman = $this->model->findOrFail($id);
            if ($serviceman->hasRole(RoleEnum::ADMIN)) {
                return redirect()->route('backend.role.index')->with('error', 'System reserved.');
            }
            $serviceman->forcedelete($id);

            DB::commit();
            return redirect()->route('backend.serviceman.index')->with('message', 'Serviceman Deleted Successfully');
        } catch (Exception $e) {

            DB::rollback();
            return back()->with('error', $e->getMessage());
        }
    }

    public function updateIsFeatured($isFeatured, $subjectId)
    {
        DB::beginTransaction();
        try {
            $category = $this->model->findOrFail($subjectId);
            $category->is_featured = $isFeatured;
            $category->save();

            DB::commit();

            return redirect()->route('backend.serviceman.index')->with('message', 'Is Featured Updated Successfully');
        } catch (Exception $e) {

            DB::rollback();

            return back()->with('error', $e->getMessage());
        }
    }

    public function updateStatus($statusVal, $subjectId)
    {
        DB::beginTransaction();
        try {

            $user = $this->model->findOrFail($request->userId);
            $user->update([
                'status' => $request->status,
            ]);
            DB::commit();

            return;
        } catch (Exception $e) {

            DB::rollback();

            return back()->with('error', $e->getMessage());
        }
    }

    public function changePassword($request, $id)
    {
        DB::beginTransaction();
        try {

            $serviceman = $this->model->findOrFail($id);
            $serviceman->update(['password' => Hash::make($request->new_password)]);

            DB::commit();

            return redirect()->route('backend.serviceman.index')->with('message', 'Password Updated Successfully');
        } catch (Exception $e) {

            DB::rollback();

            return back()->with('error', $e->getMessage());
        }
    }

    public function deleteAll($ids)
    {
        DB::beginTransaction();
        try {

            $this->model->whereNot('system_reserve', true)->whereIn('id', $ids)->delete();

            return back()->with('message', 'Roles Deleted Successfully');
        } catch (Exception $e) {

            DB::rollback();

            return back()->with('error', $e->getMessage());
        }
    }

    public function status($id, $status)
    {
        try {

            $serviceman = $this->model->findOrFail($id);
            $serviceman->update(['status' => $status]);

            if ($status != 1) {
                $serviceman->tokens()->update([
                    'expires_at' => Carbon::now(),
                ]);
            }

            return json_encode(['resp' => $serviceman]);
        } catch (Exception $e) {

            return back()->with('error', $e->getMessage());
        }
    }

    public function servicemanLocation()
    {
        try {
            $roleName = auth('web')->user()->roles->pluck('name')->first();
            $servicemen = $this->model->role(RoleEnum::SERVICEMAN)->whereNotNull('location_cordinates');

            if ($roleName == RoleEnum::PROVIDER) {
                $servicemen = $servicemen->where('provider_id', auth('web')->user()->id);
            }

            $servicemenData = $servicemen->get()->map(function ($serviceman) {
                $locationData = json_decode(!$serviceman->location_cordinates);
                return [
                    'id' => $serviceman->id,
                    'name' => $serviceman->name,
                    'email' => $serviceman->email,
                    'phone' => $serviceman->phone,
                    'vehicle_name' => $serviceman->vehicle_info?->vehicle?->name,
                    'vehicle_image' => asset('admin/images/user.png'),
                    'image' => $serviceman->getFirstMedia('image')?->getUrl() ?? asset('admin/images/user.png'),
                    'review' => $serviceman->servicemanreviews?->avg('rating'),
                    'lat' => $locationData->lat ?? null,
                    'lng' => $locationData->lng ?? null,
                ];
            });

            return view('backend.serviceman-location.index', [
                'servicemen' => $servicemenData,
            ]);
        } catch (Exception $e) {

            return back()->with('error', $e->getMessage());
        }
    }

    public function servicemanCordinates($id)
    {
        try {
        $serviceman = $this->model->findOrFail($id);

        $location = is_array($serviceman->location_cordinates) ? $serviceman->location_cordinates : json_decode($serviceman->location_cordinates, true);

        if ($location) {
            return response()->json([
                'id' => $serviceman->id,
                'name' => $serviceman->name,
                'email' => $serviceman->email,
                'phone' => $serviceman->phone,
                'image' => $serviceman->getFirstMedia('image')?->getUrl() ?? asset('admin/images/user.png'),
                'review' => $serviceman->servicemanreviews?->avg('rating'),
                'lat' => $location['lat'],
                'lng' => $location['lng'],
            ]);
        }

        return response()->json(['error' => 'Location not found'], 404);

        } catch (Exception $e) {

            return back()->with('error', $e->getMessage());
        }
    }

    public function export($request)
    {
        try {
            $format = $request->input('format', 'xlsx');

            if ($format == 'csv') {

                return Excel::download(new ServicemanExport, 'servicemen.csv');
            }
            return Excel::download(new ServicemanExport, 'servicemen.xlsx');
        } catch (Exception $e) {
            throw new Exception($e->getMessage(), $e->getCode());
        }
    }

    public function import($request)
    {
        try {
            $activeTab = $request->input('active_tab');

            $tempFile = null;

            if ($activeTab === 'direct-link') {

                $googleSheetUrl = $request->input('google_sheet_url');

                if (!$googleSheetUrl) {
                    throw new Exception(__('static.import.no_url_provided'));
                }

                if (!filter_var($googleSheetUrl, FILTER_VALIDATE_URL)) {
                    throw new Exception(__('static.import.invalid_url'));
                }

                $parsedUrl = parse_url($googleSheetUrl);
                preg_match('/\/d\/([a-zA-Z0-9-_]+)/', $parsedUrl['path'], $matches);
                $sheetId = $matches[1] ?? null;
                parse_str($parsedUrl['query'] ?? '', $queryParams);
                $gid = $queryParams['gid'] ?? 0;

                if (!$sheetId) {
                    throw new Exception(__('static.import.invalid_sheet_id'));
                }

                $csvUrl = "https://docs.google.com/spreadsheets/d/{$sheetId}/export?format=csv&gid={$gid}";

                $response = Http::get($csvUrl);

                if (!$response->ok()) {
                    throw new Exception(__('static.import.failed_to_fetch_csv'));
                }

                $tempFile = tempnam(sys_get_temp_dir(), 'google_sheet_') . '.csv';

                file_put_contents($tempFile, $response->body());
            } elseif ($activeTab === 'local-file') {
                $file = $request->file('fileImport');

                if (!$file) {
                    throw new Exception(__('static.import.no_file_uploaded'));
                }

                if ($file->getClientOriginalExtension() != 'csv') {
                    throw new Exception(__('static.import.csv_file_allow'));
                }

                $tempFile = $file->getPathname();
            } else {
                throw new Exception(__('static.import.no_valid_input'));
            }

            Excel::import(new ServicemanImport(), $tempFile);

            if ($activeTab === 'google_sheet' && file_exists($tempFile)) {
                unlink($tempFile);
            }

            return redirect()->back()->with('success', __('static.import.csv_file_import'));
        } catch (Exception $e) {
            return redirect()->back()->withErrors(['error' => $e->getMessage()]);
        }
    }
}
