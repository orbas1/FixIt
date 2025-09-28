<?php

namespace App\Repositories\Backend;

use App\Enums\RoleEnum;
use App\Helpers\Helpers;
use App\Models\Address;
use App\Models\Role;
use App\Models\User;
use Carbon\Carbon;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Prettus\Repository\Eloquent\BaseRepository;

class CustomerRepository extends BaseRepository
{
    protected $role;

    protected $address;

    public function model()
    {
        $this->address = new Address();
        $this->role = new Role();

        return User::class;
    }

    public function store($request)
    {
        DB::beginTransaction();
        try {
            $customer = $this->model->create([
                'name' => $request->name,
                'email' => $request->email,
                'code' => $request->code,
                'phone' => $request->phone,
                'is_featured' => false,
                'status' => $request->status,
                'password' => Hash::make($request->password),
                'description' => $request->description,
            ]);

            if ($request->hasFile('image') && $request->file('image')->isValid()) {
                $customer->addMediaFromRequest('image')->toMediaCollection('image');
            }
            $role = $this->role->where('name', RoleEnum::CONSUMER)->pluck('id');
            $customer->assignRole($role);

            $address = $this->address->create([
                'user_id' => $customer->id,
                'type' => $request->address_type,
                'code' => $request->alternative_code,
                'alternative_name' => $request->alternative_name,
                'alternative_phone' => $request->alternative_phone,
                'country_id' => $request->country_id,
                'state_id' => $request->state_id,
                'city' => $request->city,
                // 'area' => $request->area,
                'postal_code' => $request->postal_code,
                'address' => $request->address,
                'is_primary' => true,
            ]);

            DB::commit();

            return redirect()->route('backend.customer.index')->with('message', 'Customer Created Successfully.');
        } catch (Exception $e) {

            DB::rollback();

            return back()->with('error', $e->getMessage());
        }
    }

    public function edit($id)
    {
        $customer = $this->model->findOrFail($id);
        return view('backend.customer.edit', [
            'customer' => $customer,
            'countries' => Helpers::getCountries(),
            'address' => $this->address
                ->where('user_id', $customer->id)
                ->where('is_primary', true)->first(),
        ]);
    }

    public function update($request, $id)
    {
        DB::beginTransaction();
        try {
            $customer = $this->model->findOrFail($id);
            $customer->update($request->except(['_token', '_method', 'submit']));

            if ($request->hasFile('image') && $request->file('image')->isValid()) {
                $customer->clearMediaCollection('image');
                $customer->addMediaFromRequest('image')->toMediaCollection('image');
            }
            $role = $this->role->where('name', RoleEnum::CONSUMER)->pluck('id');
            $customer->syncRoles($role);

            $address = $this->address->where('user_id', $customer->id)->where('is_primary', true)->first();
            if ($address){
                $address->update([
                    'user_id' => $customer->id,
                    'type' => $request['address_type'],
                    'code' => $request['alternative_code'],
                    'alternative_name' => $request['alternative_name'],
                    'alternative_phone' => $request['alternative_phone'],
                    'country_id' => $request['country_id'],
                    'state_id' => $request['state_id'],
                    'city' => $request['city'],
                    // 'area' => $request['area'],
                    'postal_code' => $request['postal_code'],
                    'address' => $request['address'],
                ]);
            } else {
                $this->address->create([
                    'user_id' => $customer->id,
                    'type' => $request['address_type'],
                    'code' => $request['alternative_code'],
                    'alternative_name' => $request['alternative_name'],
                    'alternative_phone' => $request['alternative_phone'],
                    'country_id' => $request['country_id'],
                    'state_id' => $request['state_id'],
                    'city' => $request['city'],
                    // 'area' => $request['area'],
                    'postal_code' => $request['postal_code'],
                    'address' => $request['address'],
                    'is_primary' => true,
                ]);
            }
            DB::commit();

            return redirect()->route('backend.customer.index')->with('message', 'Customer Updated Successfully.');
        } catch (Exception $e) {

            DB::rollback();

            return back()->with('error', $e->getMessage());
        }
    }

    public function destroy($id)
    {
        DB::beginTransaction();
        try {

            $customer = $this->model->findOrFail($id);
            $customer->forcedelete($id);
            DB::commit();

            return redirect()->route('backend.customer.index')->with('message', 'Customer Deleted Successfully');
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
            $customer = $this->model->findOrFail($id);
            $customer->update(['status' => $status]);

            if ($status != 1) {

                $customer->tokens()->update([
                    'expires_at' => Carbon::now(),
                ]);
            }

            return json_encode(['resp' => $customer]);
            
        } catch (Exception $e) {

            return back()->with('error', $e->getMessage());
        }
    }
}
