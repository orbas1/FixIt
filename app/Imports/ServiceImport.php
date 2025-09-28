<?php

namespace App\Imports;

use App\Models\Service;
use App\Models\Category;
use App\Models\Tax;
use App\Exceptions\ExceptionHandler;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Concerns\SkipsOnError;

class ServiceImport implements ToModel, WithHeadingRow, WithValidation, SkipsOnError
{
    private $services = [];

    public function rules(): array
    {
        return [
            // 'title' => 'required|max:255',
            // 'type' => 'required|in:fixed,provider_site,remotely',
            // 'user_id' => 'exists:users,id',
            // 'required_servicemen' => 'required|numeric',
            // 'price' => 'required',
            // 'duration' => 'required',
            // 'duration_unit' => 'required|in:hours,minutes',
            // 'per_serviceman_commission' => 'required|numeric|between:0,100',
        ];
    }

    public function customValidationMessages()
    {
        return [
            // 'name.required' => __('validation.name_field_required'),
            // 'price.required' => __('validation.price_field_required'),
            // 'price.numeric' => __('validation.price_must_be_numeric'),
            // 'status.required' => __('validation.status_field_required'),
            // 'tax_id.exists' => __('validation.tax_invalid'),
        ];
    }

    /**
     * @param \Throwable $e
     */
    public function onError(\Throwable $e)
    {
        throw new ExceptionHandler($e->getMessage(), 422);
    }

    public function getImportedServices()
    {
        return $this->services;
    }

    /**
     * @param array $row
     *
     * @return \Illuminate\Database\Eloquent\Model|null
     */
    public function model(array $row)
    {
      
        $service = new Service([
            'type' => @$row['type'],
            'price' => @$row['price'],
            'title' => @$row['title'],
            'video' => @$row['video'],
            'status' => @$row['status'],
            'discount' => @$row['discount'],
            'per_serviceman_commission' => @$row['per_serviceman_commission'],
            'duration' => @$row['duration'],
            'duration_unit' => @$row['duration_unit'],
            'user_id' => @$row['user_id'],
            'description' => @$row['description'],
            'content' => @$row['content'],
            'is_featured' => @$row['is_featured'],
            'required_servicemen' => @$row['required_servicemen'],
            'service_rate' => @$row['service_rate'],
            'isMultipleServiceman' => @$row['isMultipleServiceman'],
            'is_random_related_services' => @$row['is_random_related_services'],
        ]);
        $service->save();
        $taxesArray = json_decode($row['taxes'], true);
        $servicesArray = json_decode($row['related_services'], true);
        $categoriesArray = json_decode($row['categories'], true);
        
        if (isset($row['taxes'])) {
            $service->taxes()->attach($taxesArray);
        }
        if (isset($row['related_services'])) {
            $service->related_services()->attach($servicesArray);
        }
        if (isset($row['categories'])) {
            $service->categories()->attach($categoriesArray);
        }
        
        $locale = $request->locale ?? app()->getLocale();
        
        $imageArray = json_decode($row['image'], true);
        
        if (isset($row['image'])) {
            $images = $imageArray;
            
            foreach ($images as $image) {
                $service->addMediaFromUrl($image)->withCustomProperties(['language' => $locale])->toMediaCollection('image');
            }
            
            $service->media;
        }
        
        $webImageArray = json_decode($row['web_images'], true);
        
        if ($webImageArray) {
            $images = $webImageArray;
            foreach ($images as $image) {
                $service->addMediaFromUrl($image)->withCustomProperties(['language' => $locale])->toMediaCollection('web_images');
            }
            
            $service->media;
        }
        $webThumbArray = json_decode($row['web_thumbnail'], true);
        
        if ($webThumbArray) {
            $images = $webThumbArray;
            foreach ($images as $image) {
                $service->addMediaFromUrl($image)->withCustomProperties(['language' => $locale])->toMediaCollection('web_thumbnail');
            }
            
            $service->media;
        }
        
        $ThumbArray = json_decode($row['web_thumbnail'], true);
        if ($ThumbArray) {
            $images = $ThumbArray;
            foreach ($images as $image) {
                $service->addMediaFromUrl($image)->withCustomProperties(['language' => $locale])->toMediaCollection('thumbnail');
            }
            $service->media;
        }
        
        $service = $service->fresh();
        
        $this->services[] = [
            'id' => $service?->id,
            'name' => $service?->name,
            'price' => $service?->price,
            'status' => $service?->status,
        ];
        
        return $service;
    }
}
