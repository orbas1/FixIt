<?php

namespace App\Imports;

use App\Models\Category;
use App\Exceptions\ExceptionHandler;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Concerns\SkipsOnError;


class CategoryImport implements ToModel, WithHeadingRow, WithValidation, SkipsOnError
{
    private $categories = [];

    public function rules(): array
    {
        return [
            'title' => [
                'required',
                'string',
                'max:255',
            ],
            'description' => ['nullable', 'string'],
            'zones*' => ['nullable', 'required', 'exists:zones,id,deleted_at,NULL'],
            'parent_id' => ['nullable', 'exists:categories,id,deleted_at,NULL'],
            'image' => 'nullable',
            'commission' => ['required', 'regex:/^([0-9]{1,2}){1}(\.[0-9]{1,2})?$/'],
            'category_type' => ['required', 'in:service'],
        ];
    }

    public function customValidationMessages()
    {
        return [
            'name.required' => __('validation.name_field_required'),
            'price.required' => __('validation.price_field_required'),
            'price.numeric' => __('validation.price_must_be_numeric'),
            'status.required' => __('validation.status_field_required'),
            'category_id.required' => __('validation.category_field_required'),
            'category_id.exists' => __('validation.category_invalid'),
            'tax_id.exists' => __('validation.tax_invalid'),
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
        return $this->categories;
    }

    /**
     * @param array $row
     *
     * @return \Illuminate\Database\Eloquent\Model|null
     */
    public function model(array $row)
    {

        $category = new Category([
            'title' => @$row['title'],
            'description' => @$row['description'],
            'parent_id' => @$row['parent_id'],
            'commission' => @$row['commission'],
            'status' => @$row['status'],
            'category_type' => @$row['category_type'],
            'is_featured' => @$row['is_featured'],
            'created_by' => @$row['created_by'],
        ]);

        $category->save();

        $zonesArray = json_decode($row['zones'], true);
        if (isset($row['zones'])) {
            $category->zones()->attach($zonesArray);
        }
        $locale = $request->locale ?? app()->getLocale();

        $category->addMediaFromUrl($row['media'])->withCustomProperties(['language' => $locale])->toMediaCollection('image');


        return $category;
    }
}
