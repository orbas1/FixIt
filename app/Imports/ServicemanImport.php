<?php

namespace App\Imports;

use App\Enums\UserTypeEnum;
use App\Models\User;
use App\Enums\RoleEnum;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Hash;
use App\Exceptions\ExceptionHandler;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\SkipsOnError;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use App\Models\Company;

class ServicemanImport implements ToModel, WithHeadingRow, WithValidation, SkipsOnError
{
    private $users = [];

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'unique:users,email,NULL,id,deleted_at,NULL'],
            'code' => ['required'],
            'phone' => ['required', 'digits_between:6,15', 'unique:users,phone,NULL,id,deleted_at,NULL'],
            'password' => ['required', 'min:8'],
            'status' => ['required'],
        ];
    }

    public function customValidationMessages()
    {
        return [
            'name.required' => __('validation.name_field_required'),
            'username.required' => __('validation.username_field_required'),
            'email.required' => __('validation.email_field_required'),
            'email.unique' => __('validation.email_already_taken'),
            'phone.required' => __('validation.phone_field_required'),
            'phone.unique' => __('validation.phone_already_taken'),
            'phone.digits_between' => __('validation.phone_digits_between'),
            'password.required' => __('validation.password_field_required'),
            'status.required' => __('validation.status_field_required'),
        ];
    }

    /**
     * @param \Throwable $e
     */
    public function onError(\Throwable $e)
    {
        throw new ExceptionHandler($e->getMessage(), 422);
    }

    public function getImportedUsers()
    {

        return $this->users;
    }

    /**
     * @param array $row
     *
     * @return \Illuminate\Database\Eloquent\Model|null
     */
    public function model(array $row)
    {

        $user = new User([
            'name'  => $row['name'],
            'email' => $row['email'],
            'phone' => (string) $row['phone'],
            'code' => $row['code'],
            'status' => $row['status'],
            'password' => $row['password'],
            'provider_id' => $row['provider_id']
        ]);

        $role = Role::where('id', $row['role'])->first();
        $user->assignRole($role);

        if (isset($row['image'])) {
            $media = $user->addMediaFromUrl($row['image'])->toMediaCollection('image');
        }


        $this->users[] = [
            'id' => $user?->id,
            'name'  => $user?->name,
            'email' => $user?->email,
            'code' => $user?->code,
            'phone' => $user?->phone,
            'status' => $user?->status,
            'provider_id' => $user->provider_id
        ];

        return $user;
    }
}
