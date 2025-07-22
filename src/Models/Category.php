<?php

namespace admin\categories\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Config;
use Kyslik\ColumnSortable\Sortable;

class Category extends Model
{
    use HasFactory, SoftDeletes, Sortable;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'parent_category_id',
        'title',
        'slug',
        'image',
        'sort_order',
        'status'
    ];

    protected $sortable = [
        'title',
        'parent_category_id',
        'slug',
        'status',
        'created_at',
    ];

    public function scopeFilter($query, $title)
    {
        if ($title) {
            return $query->where('title', 'like', '%' . $title . '%');
        }
        return $query;
    }
    /**
     * filter by status
     */
    public function scopeFilterByStatus($query, $status)
    {
        if (!is_null($status)) {
            return $query->where('status', $status);
        }

        return $query;
    }

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($category) {
            if (empty($category->slug)) {
                $category->slug = Str::slug($category->title, '_');
            }
        });

        static::updating(function ($category) {
            if ($category->isDirty('title')) {
                $category->slug = Str::slug($category->title, '_');
            }
        });
    }

    /**
     * Get the parent category (if any).
     */
    public function parent()
    {
        return $this->belongsTo(self::class, 'parent_category_id');
    }

    /**
     * Get the subcategories for this category.
     */
    public function children()
    {
        return $this->hasMany(self::class, 'parent_category_id');
    }
    
    public static function getPerPageLimit(): int
    {
        return Config::has('get.admin_page_limit')
            ? Config::get('get.admin_page_limit')
            : 10;
    }
}