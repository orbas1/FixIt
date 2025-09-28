<?php

namespace App\Rules;

use Closure;
use App\Models\Category;
use Illuminate\Contracts\Validation\ValidationRule;

class UniqueCategoryInZone implements ValidationRule
{
    protected $title;

    protected $categoryType;

    protected $zones;

    protected $categoryId;

    public function __construct($title, $categoryType, $zones, $categoryId = null)
    {
        $this->title = $title;
        $this->categoryType = $categoryType;
        $this->zones = $zones;
        $this->categoryId = $categoryId;
    }
    /**
     * Run the validation rule.
     *
     * @param  \Closure(string, ?string=): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $exists = Category::where('title', $this->title)
            ->where('category_type', $this->categoryType)
            ->whereNull('deleted_at')
            ->when($this->categoryId, function ($query) {
                $query->where('id', '!=', $this->categoryId);
            })
            ->whereHas('zones', function ($query) {
                $query->whereIn('zone_id', $this->zones);
            })->exists();

        if ($exists) {
            $fail(__('validation.custom.unique_category_zone'));
        }
    }
}
