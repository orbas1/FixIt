<?php

namespace App\Http\Controllers\API;

use Exception;
use App\Enums\FrontSettingsEnum;
use App\Exceptions\ExceptionHandler;
use App\Helpers\Helpers;
use App\Http\Controllers\Controller;
use App\Http\Resources\SettingResource;
use App\Models\Setting;
use Illuminate\Support\Arr;

class SettingController extends Controller
{
    public function frontSettings()
    {
        try {

            $settingValues = Helpers::getSettings();
            $filteredValues = Arr::only($settingValues, array_column(FrontSettingsEnum::cases(), 'value'));
            $filteredValues['general']['splash_screen_logo'] = $filteredValues['general']['splash_screen_logo'] ? config('app.url') . $filteredValues['general']['splash_screen_logo'] : null ;
            $filteredValues['maintenance']['image'] = $filteredValues['maintenance']['image'] ? config('app.url') . $filteredValues['maintenance']['image'] : null;

            return new SettingResource($filteredValues);

        } catch (Exception $e) {

            throw new ExceptionHandler($e->getMessage(), $e->getCode());
        }
    }

    public function getOnboardingScreens() {
        try {
            $settingValues = Setting::pluck('values')->first();

            $onboardingScreens = array_map(function ($screen) {
                $screen['image'] = config('app.url') . $screen['image'];
                return $screen;
            }, $settingValues['onboarding']);

            return $onboardingScreens;

        } catch (Exception $e) {
            throw new Exception($e->getMessage(), $e->getCode());
        }
    }

    public function getAppearance(){
        try {

            $settingValues = Setting::pluck('values')->first();
            return $settingValues['appearance'];

        } catch (Exception $e) {

            throw new ExceptionHandler($e->getMessage(), $e->getCode());
        }
    }

    public function getAdvertisement(){
        try {

            $settingValues = Setting::pluck('values')->first();
            return $settingValues['advertisement'];

        } catch (Exception $e) {

            throw new ExceptionHandler($e->getMessage(), $e->getCode());
        }
    }

}
