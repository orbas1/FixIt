<?php

namespace App\Imports;

use App\Helpers\Helpers;
use App\Models\User;
use App\Enums\RoleEnum;
use App\Models\Zone;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Hash;
use App\Exceptions\ExceptionHandler;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\SkipsOnError;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use MatanYadaev\EloquentSpatial\Objects\LineString;
use MatanYadaev\EloquentSpatial\Objects\Point;
use MatanYadaev\EloquentSpatial\Objects\Polygon;

class ZoneImport implements ToModel, WithHeadingRow, WithValidation, SkipsOnError
{
    private $zones = [];

    public function rules(): array
    {

        return [
            // 'name' => ['required', 'string', 'max:255'],
            // 'email' => ['required', 'email', 'unique:users,email,NULL,id,deleted_at,NULL'],
            // 'code' => ['required'],
            // 'phone' => ['required', 'digits_between:6,15', 'unique:users,phone,NULL,id,deleted_at,NULL'],
            // 'password' => ['required', 'min:8'],
            // 'status' => ['required'],
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

        return $this->zones;
    }

    /**
     * @param array $row
     *
     * @return \Illuminate\Database\Eloquent\Model|null
     */
    public function model(array $row)
    {

        $coordinates = json_decode($row['locations'] ?? '', true);

        $points = array_map(function ($coordinate) {
            return new Point($coordinate['lat'], $coordinate['lng']);
        }, $coordinates);
        if (head($points) != $points[count($points) - 1]) {
            $points[] = head($points);
        }

        $lineString = new LineString($points);
        $place_points = new Polygon([$lineString]);

        $zone = new Zone([
            'name'  => $row['name'],
            'place_points' => $place_points,
            'locations' => $coordinates,
            'status' => $row['status'],
        ]);
        $settings = Helpers::getSettings();

        $locale = $settings['general']['default_language']?->locale ?? app()->getLocale();
        $zone->setTranslation('name', $locale, $row['name']);
        $zone->save();
        
        $this->zones[] = [
            'name'  => $zone?->name,
            'locations' => $zone?->locations,
            'status' => $zone?->status,

        ];
        return $zone;
    }
}
