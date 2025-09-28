<?php

namespace App\Exports;

use App\Models\Category;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\FromCollection;

class CategoryExport implements FromCollection,WithMapping,WithHeadings
{

    public function collection()
    {
        $categories = Category::get();
        return $categories;
    }

    public function columns(): array
    {
        return [
            'title',
            'description',
            'parent_id',
            'commission',
            'status',
            'category_type',
            'is_featured',
            'created_by',
            'media',
            'zones'
        ];
    }

    public function map($category): array
    {
        $locale = app()->getLocale();
        $mediaItems = $category->getMedia('image')->filter(function ($media) use ($locale) {
            return $media->getCustomProperty('language') === $locale;
        });

        return [
            $category->title,
            $category->description,
            $category->parent_id,
            $category->commission,
            $category->status,
            $category->category_type,
            $category->is_featured,
            $category->created_by,
            $mediaItems?->first()?->getUrl(),
            $category->zones->pluck('id')->toArray()
        ];
    }

    public function headings(): array
    {
        return [
            'title',
            'description',
            'parent_id',
            'commission',
            'status',
            'category_type',
            'is_featured',
            'created_by',
            'media',
            'zones'
        ];
    }
}
