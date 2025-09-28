<?php

namespace App\Http\Traits;

use App\Helpers\Helpers;

trait HandlesLegacyTranslations
{
    /**
     * Get translation or fallback for translatable attributes.
     *
     * @param string $attribute
     * @param string|null $locale
     * @return mixed
     */
    public function handleModelTranslations($model, $attributes, $translatable)
    {
        $locale = app()->getLocale();
        foreach ($translatable as $key) {
            $translatedValue = $model->getTranslation($key, $locale);
            $attributes[$key] = null;
            if ($translatedValue) {
                $attributes[$key] = $translatedValue;
            } else {
                $fallbackValue = $this->getDatabaseValue($model, $key);
                if (is_string($fallbackValue)) {
                    $decodedValue = json_decode($fallbackValue, true);
                    if(is_array($decodedValue)) {
                        if(isset($decodedValue[$locale]) && empty($translatedValue)) {
                            $attributes[$key] = $fallbackValue;
                        } else {
                            $defaultLocale = Helpers::getDefaultLanguageLocale();
                            if(isset($decodedValue[$defaultLocale])) {
                                $attributes[$key] = $decodedValue[$defaultLocale];
                            } else {
                                $attributes[$key] = null;
                            }
                        }
                    }elseif ($fallbackValue && is_null($decodedValue)) {
                        $attributes[$key] = $fallbackValue;
                    }
                }
            }
        }
        return $attributes;
    }

    public function getDatabaseValue($model, $key)
    {
        return $model->getRawOriginal($key);
    }
}
