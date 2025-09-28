<?php

namespace App\Http\Controllers\Backend;

use App\DataTables\RoleDataTable;
use App\Http\Controllers\Controller;
use App\Http\Requests\Backend\CreateRoleRequest;
use App\Models\Module;
use App\Repositories\Backend\RoleRepository;
use Illuminate\Http\Request;
use App\Models\Role;

class RoleController extends Controller
{
    public $repository;

    public function __construct(RoleRepository $repository)
    {
        $this->authorizeResource(Role::class, 'role');
        $this->repository = $repository;
    }

    /**
     * Display a listing of the resource.
     */
    public function index(RoleDataTable $dataTable)
    {
        return $dataTable->render('backend.role.index');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('backend.role.create', ['modules' => $this->getModules()]);
    }

    public function getModules()
    {
        return Module::get();
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(CreateRoleRequest $request)
    {
        return $this->repository->store($request);
    }

    /**
     * Display the specified resource.
     */
    public function show(Role $role)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Role $role)
    {
        return view('backend.role.edit', ['role' => $role, 'modules' => $this->getModules()]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Role $role)
    {
        return $this->repository->update($request->all(), $role?->id);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Role $role)
    {
        return $this->repository->destroy($role?->id);
    }

    public function deleteRows(Request $request)
    {
        try {
            foreach ($request->id as $row => $key) {
                $role = Role::find($request->id[$row]);
                $role->delete();
            }
        } catch (\Exception $e) {
            throw $e;
        }
    }
}
