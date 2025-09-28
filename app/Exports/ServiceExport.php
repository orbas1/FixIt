<?php

namespace App\Exports;
use App\Models\Service;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\FromCollection;

class ServiceExport implements FromCollection,WithMapping,WithHeadings
{
    /**
    * @return \Illuminate\Support\Collection
    */
    public function collection()
    {
        $services = Service::get();

        return $services;
    }

    /**
     * Specify the columns for the export.
     *
     * @return array
     */
    public function columns(): array
    {
        return [
            'type',
            'price',
            'title',
            'status',
            'discount',
            'per_serviceman_commission',
            'duration',
            'user_id',
            'description',
            'speciality_description',
            'is_featured',
            'duration_unit',
            'service_rate',
            'required_servicemen',
            'created_by_id',
            'is_random_related_services',
            'categories',
            'taxes',
            'related_services',
            'image',
            'web_images',
            'web_thumbnail',
            'thumbnail',
        ];
    }

    public function map($service): array
    {
        $locale = app()->getLocale();
        $mediaItems = $service->getMedia('image')->filter(function ($media) use ($locale) {
            return $media->getCustomProperty('language') === $locale;
        });

        $imageUrls = $mediaItems->pluck('original_url')->toArray();
        $mediaItems = $service->getMedia('thumbnail')->filter(function ($media) use ($locale) {
            return $media->getCustomProperty('language') === $locale;
        });

        $thumbUrls = $mediaItems->pluck('original_url')->toArray();
        $mediaItems = $service->getMedia('web_thumbnail')->filter(function ($media) use ($locale) {
            return $media->getCustomProperty('language') === $locale;
        });

        $web_thumb = $mediaItems->pluck('original_url')->toArray();

        $mediaItems = $service->getMedia('web_images')->filter(function ($media) use ($locale) {
            return $media->getCustomProperty('language') === $locale;
        });

        $web_images = $mediaItems->pluck('original_url')->toArray();

        $services =  [
            $service?->type,
            $service?->price,
            $service?->title,
            $service?->status,
            $service?->discount,
            $service?->per_serviceman_commission,
            $service?->duration,
            $service?->user_id,
            $service?->description,
            $service?->speciality_description,
            $service?->is_featured,
            $service?->duration_unit,
            $service?->service_rate,
            $service?->required_servicemen,
            $service?->created_by_id,
            $service?->is_random_related_services,
            $service?->categories->pluck('id')->toArray(),
            $service?->taxes->pluck('id')->toArray(),
            $service?->related_services->pluck('id')->toArray(),
            $imageUrls,
            $thumbUrls,
            $web_thumb,
            $web_images
        ];

        return $services;
    }

     /**
     * Get the headings for the export file.
     *
     * @return array
     */
    public function headings(): array
    {
        return [
            'type',
            'price',
            'title',
            'status',
            'discount',
            'per_serviceman_commission',
            'duration',
            'user_id',
            'description',
            'speciality_description',
            'is_featured',
            'duration_unit',
            'service_rate',
            'required_servicemen',
            'created_by_id',
            'is_random_related_services',
            'categories',
            'taxes',
            'related_services',
            'image',
            'web_images',
            'web_thumbnail',
            'thumbnail',
        ];
    }


}
