<?php

namespace App\Imports;

use App\Enums\UserTypeEnum;
use App\Helpers\Helpers;
use App\Models\Service;
use App\Models\ServicePackage;
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

class ServicePackageImport implements ToModel, WithHeadingRow, WithValidation, SkipsOnError
{
    private $users = [];

    public function rules(): array
    {
        return [
            'title' => 'required|max:255',
            'provider_id' => 'required|exists:users,id',
            // 'service_id' => 'required|exists:services,id',
            'price' => 'required|numeric',
            'start_end_date' => 'required',
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

        $servicePackage = new ServicePackage([
            'title' => $row['title'],
            'hexa_code' => $row['hexa_code'],
            'bg_color' => $row['bg_color'],
            'price' => $row['price'],
            'discount' => $row['discount'],
            'description' => $row['description'],
            'is_featured' => $row['is_featured'],
            'provider_id' => $row['provider_id'],
            'started_at' => $row['started_at'],
            'ended_at' => $row['ended_at'],
            'status' => $row['status'],
        ]);
        $servicePackage->save();

        if (isset($row['service_ids'])) {
            $service_ids = json_decode($row['service_ids'], true);
            $servicePackage->services()->attach($service_ids);
            $servicePackage->services;
        }


        $locale = $settings['general']['default_language']?->locale ?? app()->getLocale();

        if (isset($row['image'])) {
            $media = $servicePackage->addMediaFromUrl($row['image'])->withCustomProperties(['language' => $locale])->toMediaCollection('image');

        }
        $servicePackage->setTranslation('title', $locale, $row['title']);
        $servicePackage->setTranslation('description', $locale, $row['description']);
        $servicePackage->save();
        return $servicePackage;
    }
}


