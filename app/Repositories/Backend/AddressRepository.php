<?php

namespace App\Repositories\Backend;

use App\Enums\RoleEnum;
use App\Exceptions\ExceptionHandler;
use App\Helpers\Helpers;
use App\Models\Address;
use Exception;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Prettus\Repository\Eloquent\BaseRepository;
use Symfony\Component\HttpFoundation\Response;

class AddressRepository extends BaseRepository
{
    public function model()
    {
        return Address::class;
    }

    public function create(array $attributes)
    {
    }

    public function isProviderCanCreate()
    {
        if (Helpers::isUserLogin()) {
            $isAllowed = true;
            $roleName = Helpers::getCurrentRoleName();
            if ($roleName == RoleEnum::PROVIDER) {
                $isAllowed = false;
                $provider = Auth::user();
                $maxItems = $provider?->addresses()?->count() ?? 0;
                if (Helpers::isModuleEnable('Subscription')) {
                    if (function_exists('isPlanAllowed')) {
                        $isAllowed = isPlanAllowed('allowed_max_addresses', $maxItems, $provider?->id);
                    }
                }

                if (!$isAllowed) {
                    $settings = Helpers::getSettings();
                    $max_addresses = $settings['default_creation_limits']['allowed_max_addresses'];
                    if ($max_addresses > $maxItems) {
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
                if ($request->address_type === 'provider') {
                    if ($request->is_primary == true) {
                        $this->model->query()->where('user_id', $request->id)->update([
                            'is_primary' => false,
                        ]);
                    }

                    $address = $this->model->create([
                        'user_id' => $request->id,
                        'type' => $request->type,
                        'postal_code' => $request->postal_code,
                        'country_id' => $request->country_id,
                        'state_id' => $request->state_id,
                        'city' => $request->city,
                        'code' => $request->code,
                        'address' => $request->address,
                        'street_address' => $request->street_address,
                        'alternative_name' => $request->alternative_name,
                        'alternative_phone' => $request->alternative_phone,
                        'is_primary' => $request->is_primary,
                    ]);
                } elseif ($request->address_type === 'service') {
                    if ($request->is_primary == true) {
                        $this->model->query()->where('service_id', $request->id)->update(['is_primary' => false]);
                    }
                    $address = $this->model->create([
                        'service_id' => $request->id,
                        'type' => $request->type,
                        'postal_code' => $request->postal_code,
                        'country_id' => $request->country_id,
                        'state_id' => $request->state_id,
                        'city' => $request->city,
                        'code' => $request->code,
                        'address' => $request->address,
                        'street_address' => $request->street_address,
                        'alternative_name' => $request->alternative_name,
                        'alternative_phone' => $request->alternative_phone,
                        'is_primary' => $request->is_primary,
                    ]);
                } else {
                    return response()->json([
                        'success' => false,
                        'message' => 'Type not found!',
                    ]);
                }

                DB::commit();
                return back()->with('active_tab', 'address_tab');
            }

            throw new Exception(__('static.not_allow_for_creation'), 400);
        } catch (Exception $e) {
            DB::rollBack();
            return back()->with('error', $e->getMessage());
        }
    }

    public function edit($attributes, $id)
    {
        $address = $this->model->findOrFail($id);
        $countries = Helpers::getCountries();

        return view('backend.address.edit', compact('address', 'attributes', 'countries'));
    }

    public function update($request, $id)
    {
        DB::beginTransaction();
        try {
            $address = $this->model->findOrFail($id);
            if ($request['address_type'] === 'provider') {
                if (isset($request['is_primary']) == true) {
                    if (($request['is_primary']) == true) {
                        $this->model->query()->where('user_id', $request['id'])->update(['is_primary' => false]);
                    }
                }
                $address->update([
                    'user_id' => $request['id'],
                    'postal_code' => $request['postal_code'],
                    'country_id' => $request['country_id'],
                    'state_id' => $request['state_id'],
                    'city' => $request['city'],
                    'address' => $request['address'],
                    'street_address' => $request['street_address'],
                    'is_primary' => $request['is_primary'] ?? $address?->is_primary,
                ]);

            } elseif ($request['address_type'] === 'service') {

                if (isset($request['is_primary']) == true) {
                    if (($request['is_primary']) == true) {
                        $this->model->query()->where('service_id', $request['id'])->update(['is_primary' => false]);
                    }
                }

                $address->update([
                    'service_id' => $request['id'],
                    'postal_code' => $request['postal_code'],
                    'country_id' => $request['country_id'],
                    'state_id' => $request['state_id'],
                    'city' => $request['city'],
                    'address' => $request['address'],
                    'street_address' => $request['street_address'],
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Type not found!',
                ]);
            }

            DB::commit();

            return back()->with('active_tab', 'address_tab');
        } catch (Exception $e) {

            DB::rollback();
            return back()->with('error', $e->getMessage());
        }
    }

    public function isPrimary($id)
    {
        DB::beginTransaction();
        try {
            $primaryAddress = $this->model->query()->where('user_id', auth()->user()->id);
            $primaryAddress->update(['is_primary' => false]);
            $addressExist = $this->model->query()->where('user_id', auth()->user()->id)->where('id', $id);
            $makePrimaryAddress = $addressExist->update(['is_primary' => true]);
            if (! $makePrimaryAddress) {
                throw new ExceptionHandler(__('errors.invalid_address_id'), 409);
            }
            DB::commit();

            return response()->json([
                'message' => __('static.address.primary_address_updated_successfully'),
                'status' => true,
            ]);
        } catch (Exception $e) {

            return back()->with('error', $e->getMessage());
        }
    }

    public function destroy($id)
    {
        try {
            $address = $this->model->findOrFail($id);
            if (!$address) {
                throw new ExceptionHandler(__('static.address.id_not_valid'), Response::HTTP_BAD_REQUEST);
            }
            $address->destroy($id);

            return back()->with('active_tab', 'address_tab');
        } catch (Exception $e) {

            return back()->with('error', $e->getMessage());
        }
    }
}
