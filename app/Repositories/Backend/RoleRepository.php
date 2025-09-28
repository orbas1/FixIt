<?php

namespace App\Repositories\Backend;

use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Request;
use Prettus\Repository\Eloquent\BaseRepository;
use Spatie\Permission\Models\Role;

class RoleRepository extends BaseRepository
{
    public function model()
    {
        return Role::class;
    }

    public function show($id)
    {
        try {

            return $this->model->with('permissions')->findOrFail($id);

        } catch (Exception $e) {

            return back()->with('error', $e->getMessage());
        }
    }

    public function store($request)
    {
        DB::beginTransaction();
        try {
            if (Request::exists('permissions')) {
                $role = $this->model->create([
                    'guard_name' => 'web',
                    'name' => $request->name,
                ]);
                $role->givePermissionTo($request->permissions);
            } else {
                return back()->with('warning', 'At least one permission is required');
            }

            DB::commit();
            return redirect()->route('backend.role.index')->with('message', 'Role Created Successfully.');

        } catch (Exception $e) {

            DB::rollback();
            return back()->with('error', $e->getMessage());
        }
    }

    public function update($request, $id)
    {
        DB::beginTransaction();
        try {

            $role = $this->model->findOrFail($id);

            if ($role->system_reserve) {
                return redirect()->route('backend.role.index')->with('error', 'This Role Cannot be Update. It is System reserved.');
            }

            $role->syncPermissions($request['permissions']);
            $role->update($request);

            DB::commit();
            return redirect()->route('backend.role.index')->with('message', 'Role Updated Successfully');

        } catch (Exception $e) {

            DB::rollback();
            return back()->with('error', $e->getMessage());
        }
    }

    public function destroy($id)
    {
        DB::beginTransaction();
        try {

            $role = $this->model->findOrFail($id);
            if ($role->system_reserve) {
                return redirect()->route('backend.role.index')->with('error', 'This Role Cannot be deleted. It is System reserved.');
            }

            $role->destroy($id);
            DB::commit();

            return redirect()->route('backend.role.index')->with('message', 'Role Deleted Successfully');

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
}
