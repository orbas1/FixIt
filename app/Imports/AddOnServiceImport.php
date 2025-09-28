<?php

namespace App\Imports;

use App\Enums\UserTypeEnum;
use App\Helpers\Helpers;
use App\Models\Service;
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

class AddOnServiceImport implements ToModel, WithHeadingRow, WithValidation, SkipsOnError
{
    private $users = [];

    public function rules(): array
    {
        return [
            'thumbnail' => 'required|mimetypes:image/jpeg,image/png|max:2048',
            'title' => 'required|string|max:255',
            'price' => 'required|numeric|min:1',
            'parent_id' => 'required|exists:services,id',
        ];
    }

    public function customValidationMessages()
    {
        return [
            
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
        $service = new Service([
            'title'  => $row['title'],
            'price'  => $row['price'],
            'parent_id'  => $row['parent_id'],
            'user_id'  => $row['user_id'],

        ]);
        $settings = Helpers::getSettings();

        $locale = $settings['general']['default_language']?->locale ?? app()->getLocale();

        if (isset($row['image'])) {
            $media = $service->addMediaFromUrl($row['image'])->withCustomProperties(['language' => $locale])->toMediaCollection('thumbnail');

        }
        $service->setTranslation('title', $locale, $row['title']);

        return $service;
    }
}
