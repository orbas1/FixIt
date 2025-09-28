<?php

namespace App\Models;

use App\Enums\CategoryType;
use App\Http\Traits\HandlesLegacyTranslations;
use Cviebrock\EloquentSluggable\Sluggable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\Translatable\HasTranslations;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class Category extends Model implements HasMedia
{
    use HasFactory, InteractsWithMedia, LogsActivity, Sluggable, HasTranslations, HandlesLegacyTranslations;

    public $translatable = [
        'title',
        'description',
    ];

    public $fillable = [
        'title',
        'slug',
        'description',
        'parent_id',
        'meta_title',
        'meta_description',
        'type',
        'status',
        'created_by',
        'is_featured',
        'status',
        'commission',
        'commission_type',
        'category_type',
    ];

    protected $with = [
        // 'media',
        // 'zones',
    ];

    protected $withCount = [
        'services'
    ];

    protected $casts = [
        'status' => 'integer',
        'parent_id' => 'integer',
        'created_by' => 'integer',
        'commission' => 'float',
    ];

    public function sluggable(): array
    {
        return [
            'slug' => [
                'source' => 'title',
                'onUpdate' => true,
            ],
        ];
    }

    public static function getAllChildCategories()
    {
        return self::whereNotNull('parent_id')->where('category_type', CategoryType::SERVICE)->get();
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logAll()
            ->useLogName('Category')
            ->setDescriptionForEvent(fn(string $eventName) => "{$this->title} - Category has been {$eventName}");
    }

    public function toArray()
    {
        $attributes = parent::toArray();
        $locale = request()->header('Accept-Lang') ? request()->header('Accept-Lang') : request('locale');

        if (isset($attributes['media']) && is_array($attributes['media'])) {
            $attributes['media'] = array_filter($attributes['media'], function ($media) use ($locale) {
                return isset($media['custom_properties']['language'])
                    && $media['custom_properties']['language'] === $locale;
            });

            $attributes['media'] = array_values($attributes['media']);
        }

        return  $this->handleModelTranslations($this, $attributes, $this->translatable);
    }

    public function scopeWithOutParent($query)
    {
        return $query->whereNull('parent_id');
    }

    public static function getHierarchy($zoneId): array
    {
        return self::buildHierarchy($zoneId);
    }


    private static function buildCategoryHierarchy($categoryType = null): array
    {
        if($categoryType){
            $categories = self::where('category_type', CategoryType::BLOG)
            ->where('status', true)->get();
        } else {
            $categories = self::where('category_type', CategoryType::SERVICE)->get();
        }

        $categoryHierarchy = [];

        foreach ($categories as $category) {
            if (is_null($category->parent_id)) {
                $categoryHierarchy[$category->id] = [
                    'title' => $category->title,
                    'children' => []
                ];
            } else {
                if (isset($categoryHierarchy[$category->parent_id])) {
                    $categoryHierarchy[$category->parent_id]['children'][] = [
                        'id' => $category->id,
                        'title' => $category->title
                    ];
                }
            }
        }

        return $categoryHierarchy;
    }


    public static function getCategoryDropdownOptions($categoryType = null): array
    {

        $categoryHierarchy = self::buildCategoryHierarchy($categoryType);
        $dropdownOptions = [];

        foreach ($categoryHierarchy as $parentId => $category) {
            $dropdownOptions[$parentId] = $category['title'];

            foreach ($category['children'] as $child) {
                $dropdownOptions[$child['id']] = ' - ' . $child['title'];
            }
        }

        return $dropdownOptions;
    }


    private static function buildHierarchy($zoneId): array
    {
        if ($zoneId) {
            $categories = self::with('zones')
                ->where('category_type', CategoryType::SERVICE)
                ->whereHas('zones', function ($zones) use ($zoneId) {
                    $zones->whereIn('zone_id', $zoneId);
                })
                ->get();
        } else {
            $categories = self::where('category_type', CategoryType::SERVICE)->get();
        }
        $categoryHierarchy = [];


        $categoriesById = $categories->keyBy('id');


        foreach ($categories as $category) {
            if (is_null($category->parent_id)) {
                $categoryHierarchy[$category->id] = [
                    'title' => $category->title,
                    'children' => self::buildChildren($category, $categoriesById)
                ];
            }
        }

        return $categoryHierarchy;
    }

    private static function buildChildren($category, $categoriesById): array
    {
        $children = [];

        foreach ($categoriesById as $childCategory) {
            if ($childCategory->parent_id == $category->id) {
                $children[] = [
                    'id' => $childCategory->id,
                    'title' => $childCategory->title,
                    'children' => self::buildChildren($childCategory, $categoriesById)
                ];
            }
        }

        return $children;
    }

    public static function getDropdownOptions($zoneId = []): array
    {
        $categoryHierarchy = self::getHierarchy($zoneId);
        $dropdownOptions = [];

        foreach ($categoryHierarchy as $parentId => $category) {
            $dropdownOptions[$parentId] = $category['title'];
            self::addChildrenToDropdown($category['children'], $dropdownOptions, ' - ');
        }

        return $dropdownOptions;
    }

    private static function addChildrenToDropdown(array $children, array &$dropdownOptions, string $prefix)
    {
        foreach ($children as $child) {
            $dropdownOptions[$child['id']] = $prefix . $child['title'];
            if (!empty($child['children'])) {
                self::addChildrenToDropdown($child['children'], $dropdownOptions, $prefix . ' - ');
            }
        }
    }


    private function getCategories(): array
    {
        $mainCategories = self::whereNull('parent_id')->get();
        foreach ($mainCategories as $category) {
            $this->categories[] = $category->toArray();
            $this->getParentCategories($category, 3);
        }

        return $this->categories;
    }

    private function getParentCategories($category, $level)
    {
        if ($subCategories = $category->hasSubCategories) {
            $level++;
            foreach ($subCategories as $subCategory) {
                $subCategory->title = str_repeat('- ', $level).$subCategory->title;
                $this->categories[] = $subCategory;
                $this->getParentCategories($subCategory, $level);
            }
        }
    }

    public function hasSubCategories()
    {
        return $this->hasMany($this, 'parent_id');
    }

    public function childs()
    {
        return $this->hasMany(Category::class, 'parent_id', 'id');
    }

    public function scopeActive($query, $value)
    {
        return $query->where('status', $value);
    }

    public function blogs(): BelongsToMany
    {
        return $this->belongsToMany(Blog::class, 'blog_categories');
    }

    public function services()
    {
        return $this->belongsToMany(Service::class, 'service_categories');
    }

    public function zones(): BelongsToMany
    {
        return $this->belongsToMany(Zone::class, 'category_zones', 'category_id', 'zone_id');
    }
}
