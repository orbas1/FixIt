<?php

namespace App\Repositories\API;

use Exception;
use Prettus\Repository\Eloquent\BaseRepository;
use Spatie\Permission\Models\Role;

class RoleRepository extends BaseRepository
{
    public function model()
    {
        return Role::class;
    }

    public function getRolesWithPermissions()
    {
        try {

            return $this->model->with('permissions')->get();
        } catch (Exception $e) {

            throw $e;
        }
    }

    public function show($id) {}

    public function store($request) {}

    public function update($request, $id) {}

    public function destroy($id) {}
}
