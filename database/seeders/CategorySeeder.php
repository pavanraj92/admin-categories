<?php

namespace Admin\Categories\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Carbon\Carbon;
use Illuminate\Support\Facades\Session;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $now = Carbon::now();

        $categoriesByIndustry = [
            'ecommerce' => [
                ['title' => 'Fashion', 'children' => ['Men', 'Women', 'Kids']],
                ['title' => 'Electronics', 'children' => ['Mobiles', 'Laptops', 'Cameras']],
                ['title' => 'Home & Kitchen', 'children' => ['Furniture', 'Appliances', 'Decor']],
            ],
            'education' => [
                ['title' => 'Programming', 'children' => ['PHP', 'JavaScript', 'Python']],
                ['title' => 'Mathematics', 'children' => ['Algebra', 'Geometry', 'Statistics']],
            ],
        ];

        // Example: industry slug from session or env
        $industry = Session::get('industry', 'ecommerce'); // default ecommerce

        if (isset($categoriesByIndustry[$industry])) {
            foreach ($categoriesByIndustry[$industry] as $sortOrder => $category) {
                // Insert parent category
                $parentId = DB::table('categories')->updateOrInsert(
                    ['slug' => Str::slug($category['title'])],
                    [
                        'parent_category_id' => null,
                        'title'       => $category['title'],
                        'slug'        => Str::slug($category['title']),
                        'image'       => null,
                        'sort_order'  => $sortOrder + 1,
                        'status'      => 1,
                        'created_at'  => $now,
                        'updated_at'  => $now,
                    ]
                );

                // Get parent id (for child categories)
                $parentCategory = DB::table('categories')
                    ->where('slug', Str::slug($category['title']))
                    ->first();

                if (!empty($category['children']) && $parentCategory) {
                    foreach ($category['children'] as $childSort => $child) {
                        DB::table('categories')->updateOrInsert(
                            ['slug' => Str::slug($child)],
                            [
                                'parent_category_id' => $parentCategory->id,
                                'title'       => $child,
                                'slug'        => Str::slug($child),
                                'image'       => null,
                                'sort_order'  => $childSort + 1,
                                'status'      => 1,
                                'created_at'  => $now,
                                'updated_at'  => $now,
                            ]
                        );
                    }
                }
            }
        }
    }
}