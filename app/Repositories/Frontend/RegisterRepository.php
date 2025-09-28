<?php

namespace App\Repositories\Frontend;

use App\Events\CreateUserEvent;
use App\Exceptions\ExceptionHandler;
use App\Helpers\Helpers;
use App\Models\Address;
use App\Models\User;
use Exception;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Prettus\Repository\Eloquent\BaseRepository;
use Spatie\Permission\Models\Role;

class RegisterRepository extends BaseRepository
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
            $user = $this->model->create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'status' => true,
            ]);

            $role = Role::findByName('user');
            $user->assignRole($role);
            event(new CreateUserEvent($user));


            if (Helpers::walletIsEnable()) {
                $user->wallet()?->create();
                $user->wallet;
            }

            DB::commit();
            Auth::login($user);
            return to_route('frontend.account.profile.index');

        } catch (Exception $e) {

            DB::rollback();
            throw new ExceptionHandler($e->getMessage(), $e->getCode());
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

            throw new ExceptionHandler($e->getMessage(), $e->getCode());
        }
    }
}
