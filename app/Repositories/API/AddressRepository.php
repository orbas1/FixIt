<?php

namespace App\Repositories\API;

use App\Enums\RoleEnum;
use App\Exceptions\ExceptionHandler;
use Illuminate\Support\Facades\Validator;
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
                $user = Helpers::getCurrentUser();
                if ($request->is_primary) {
                    $this->model->query()->where('user_id', Helpers::getCurrentUserId())->update(['is_primary' => false]);
                }

                if ($request->role_type === 'provider') {
                    $address = $this->model->create([
                        'user_id' => $user->id,
                        'latitude' => $request->latitude,
                        'longitude' => $request->longitude,
                        'type' => $request->type,
                        'postal_code' => $request->postal_code,
                        'country_id' => $request->country_id,
                        'state_id' => $request->state_id,
                        'status' => $request->status,
                        'city' => $request->city,
                        'code' => $request->code,
                        'address' => $request->address,
                        'area' => $request->area,
                        'availability_radius' => $request->availability_radius,
                        'is_primary' => $request->is_primary,
                    ]);

                } else {
                    $address = $this->model->create([
                        'user_id' => $user->id,
                        'latitude' => $request->latitude,
                        'longitude' => $request->longitude,
                        'type' => $request->type,
                        'postal_code' => $request->postal_code,
                        'country_id' => $request->country_id,
                        'state_id' => $request->state_id,
                        'status' => $request->status,
                        'city' => $request->city,
                        'code' => $request->code,
                        'address' => $request->address,
                        'area' => $request->area,
                        'availability_radius' => $request->availability_radius,
                        'alternative_name' => $request->alternative_name,
                        'alternative_phone' => $request->alternative_phone,
                        'is_primary' => $request->is_primary,
                    ]);
                }

                DB::commit();

                return response()->json([
                    'message' => __('static.address.create_successfully'),
                    'address' => $address,
                ]);
            }

            throw new Exception(__('static.not_allow_for_creation'), 400);
        } catch (Exception $e) {
            DB::rollBack();
            throw new ExceptionHandler($e->getMessage(), $e->getCode());
        }
    }

    public function update($request, $id)
    {
        DB::beginTransaction();
        try {
            $address = $this->model->findOrFail($id);
            if (auth()->user()->id != $address->user_id) {
                throw new ExceptionHandler(__('static.address.address_id_not_valid'), Response::HTTP_BAD_REQUEST);
            }

            if (isset($request['is_primary']) == true) {
                if (($request['is_primary']) == true) {
                    $this->model->query()->where('user_id', Helpers::getCurrentUserId())->update(['is_primary' => false]);
                }
            }
            $address->update($request);

            DB::commit();

            return response()->json([
                'message' => __('static.address.update_successfully'),
                'address' => $address,
            ]);

        } catch (Exception $e) {

            DB::rollback();
            throw new ExceptionHandler($e->getMessage(), $e->getCode());
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
            if (!$makePrimaryAddress) {
                throw new ExceptionHandler(__('validation.invalid_address_id'), 409);
            }
            DB::commit();

            return response()->json([
                'message' => __('static.address.primary_address_updated_successfully'),
                'status' => true,
            ]);

        } catch (Exception $e) {

            throw new ExceptionHandler($e->getMessage(), $e->getCode());
        }
    }

    public function destroy($id)
    {
        try {
            $address = $this->model->findOrFail($id);
            if (!$address) {
                throw new ExceptionHandler(__('static.address.id_not_valid'), Response::HTTP_BAD_REQUEST);
            }

            if ($address->is_primary) {
                throw new ExceptionHandler(__('static.address.cannot_delete_primary'), Response::HTTP_BAD_REQUEST);
            }

            $address->destroy($id);

            return response()->json([
                'message' => __('static.address.delete_successfully'),
            ]);
        } catch (Exception $e) {

            throw new ExceptionHandler($e->getMessage(), $e->getCode());
        }
    }

    public function changeAddressStatus($request, $id)
    {
        DB::beginTransaction();
        try {
            $validator = Validator::make($request->all(), [
                'status' => 'required|integer',
            ]);

            if ($validator->fails()) {
                throw new Exception($validator->messages()->first(), 422);
            }

            $address = $this->model->where('id', $id);
            $address->update(['status' => $request->status]);
            DB::commit();

            return response()->json([
                'success' => true,
                'message' => __('static.address.status_update_successfully'),
            ]);
        } catch (Exception $e) {

            throw new ExceptionHandler($e->getMessage(), $e->getCode());
        }
    }
}
