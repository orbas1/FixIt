<?php

namespace App\Repositories\Backend;

use App\Enums\RoleEnum;
use App\Models\User;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Prettus\Repository\Eloquent\BaseRepository;
use Spatie\Permission\Models\Role;

class ProviderTypeRepository extends BaseRepository
{
    protected $role;

    public function model()
    {
        $this->role = new Role();

        return User::class;
    }

    public function store($request)
    {
        DB::beginTransaction();
        try {
            $user = $this->model->create([
                'name' => $request->name,
                'email' => $request->email,
                'country_id' => $request->country_id,
                'state_id' => $request->state_id,
                'city' => $request->city,
                'phone' => (string) $request->phone,
                'code' => $request->countryCode,
                'status' => $request->status,
                'address' => $request->address,
                'password' => Hash::make($request->password),
            ]);

            $role = $this->role->where('name', RoleEnum::PROVIDER)->first();
            if ($request->role) {
                $role = $this->role->findOrFail($request->role);
            }
            $user->assignRole($role);

            if ($request->hasFile('image') && $request->file('image')->isValid()) {
                $user->addMediaFromRequest('image')->toMediaCollection('image');
            }

            DB::commit();

            return redirect()->route('backend.provider.index')->with('message', 'Provider Created Successfully.');
        } catch (Exception $e) {

            DB::rollback();

            return back()->with('error', $e->getMessage());
        }
    }

    public function update($request, $id)
    {
        DB::beginTransaction();
        try {

            $user = $this->model->findOrFail($id);
            if ($user->system_reserve) {
                return redirect()->route('backend.user.index')->with('error', 'This User Cannot be Update. It is System reserved.');
            }

            $user->update($request);
            $role = $this->role->find($request['role']);
            $user->syncRoles($role);

            DB::commit();

            return redirect()->route('backend.user.index')->with('message', 'User Updated Successfully.');
        } catch (Exception $e) {

            DB::rollback();

            return back()->with('error', $e->getMessage());
        }
    }

    public function destroy($id)
    {
        try {

            $user = $this->model->findOrFail($id);
            if ($user->hasRole(RoleEnum::ADMIN)) {
                return redirect()->route('backend.role.index')->with('error', 'System reserved.');
            }

            $user->destroy($id);

            return redirect()->route('backend.provider.index')->with('message', 'User Deleted Successfully');
        } catch (Exception $e) {

            return back()->with('error', $e->getMessage());
        }
    }

    public function updatePassword($request, $id)
    {
        try {

            $this->model->findOrFail($id)->update([
                'password' => Hash::make($request->new_password),
            ]);

            return back()->with('message', 'User Password Update Successfully.');
        } catch (Exception $e) {

            return back()->with('error', $e->getMessage());
        }
    }
}
