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

class ProviderImport implements ToModel, WithHeadingRow, WithValidation, SkipsOnError
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
        if ($row['type'] === UserTypeEnum::COMPANY) {

            $company = Company::create([
                'name' => $row['company_name'],
                'email' => $row['company_email'],
                'code' => (string)$row['company_code'],
                'phone' => $row['company_phone'],
                'description' => $row['company_description'],
            ]);

        }

        $user = new User([
            'company_id' => $company->id ?? null,
            'experience_interval' => $row['experience_interval'],
            'experience_duration' => $row['experience_duration'],
            'name' => $row['name'],
            'email' => $row['email'],
            'phone' => $row['phone'],
            'code' => $row['code'],
            'status' => $row['status'],
            'type' => $row['type'],
            'password' => $row['password'],
            'description' => $row['description'],
        ]);


        $role = Role::where('id', $row['role_id'])->first();
        $user->assignRole($role);
        $user->save();


        if (isset($row['image'])) {
            $media = $user->addMediaFromUrl($row['image'])->toMediaCollection('image');

        }

        $zonesArray = json_decode($row['zones'], true);

        if ($row['zones']!==null && isset($row['zones'])) {
            $user->zones()->attach($zonesArray);
        }


        $user = $user->fresh();


        $this->users[] = [
            'id' => $user?->id,
            'name'  => $user?->name,
            'email' => $user?->email,
            'code' => $user?->code,
            'phone' => $user?->phone,
            'status' => $user?->status,
        ];
        return $user;
    }
}
