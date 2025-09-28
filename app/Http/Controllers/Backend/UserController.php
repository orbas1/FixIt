<?php

namespace App\Http\Controllers\Backend;

use App\Models\User;
use Illuminate\Http\Request;
use App\DataTables\UserDataTable;
use App\Http\Controllers\Controller;
use App\Http\Requests\Backend\CreateUserRequest;
use App\Http\Requests\Backend\UpdateUserRequest;
use App\Http\Requests\UpdatePasswordRequest;
use App\Repositories\Backend\UserRepository;
use Illuminate\Contracts\Support\Renderable;

class UserController extends Controller
{
    private $repository;

    public function __construct(UserRepository $repository)
    {
        $this->authorizeResource(User::class, 'user');
        $this->repository = $repository;
    }

    /**
     * Display a listing of the resource.
     *
     * @return Renderable
     */
    public function index(UserDataTable $dataTable)
    {
        return $dataTable->render('backend.user.index');
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return Renderable
     */
    public function create()
    {
        return $this->repository->create();
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  Request  $request
     * @return Renderable
     */
    public function store(CreateUserRequest $request)
    {
        return $this->repository->store($request);
    }

    /**
     * Show the specified resource.
     *
     * @param  int  $id
     * @return Renderable
     */
    public function show(User $user)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return Renderable
     */
    public function edit(User $user)
    {
        
        return $this->repository->edit($user?->id);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  Request  $request
     * @param  int  $id
     * @return Renderable
     */
    public function update(UpdateUserRequest $request,User $user)
    {
        return $this->repository->update($request, $user->id);
    }

    public function status(Request $request,$id)
    {
        return $this->repository->status($id, $request->status);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return Renderable
     */
    public function destroy(User $user)
    {
        return $this->repository->destroy($user->id);
    }

    public function updatePassword(UpdatePasswordRequest $request, $id)
    {
        return $this->repository->updatePassword($request, $id);
    }

    public function deleteRows(Request $request)
    {
        try {
            foreach ($request->id as $row => $key) {
                $user = User::find($request->id[$row]);
                $user->delete();
            }
        } catch (\Exception $e) {
            throw $e;
        }
    }

    public function export(Request $request)
    {
        return $this->repository->export($request);
    }

    public function import(Request $request)
    {
        return $this->repository->import($request);
    }
}
