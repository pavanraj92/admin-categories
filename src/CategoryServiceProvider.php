<?php

namespace admin\categories;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class CategoryServiceProvider extends ServiceProvider
{
    public function boot()
    {
        // Load routes, views, migrations from the package
        // $this->loadRoutesFrom(__DIR__.'/routes/web.php');
        $this->registerAdminRoutes();
        $this->loadViewsFrom(__DIR__.'/../resources/views', 'category');
        $this->loadMigrationsFrom(__DIR__.'/../database/migrations');
        $this->mergeConfigFrom(__DIR__.'/../config/category.php', 'category.constants');
        

        $this->publishes([  
            __DIR__.'/../resources/views' => resource_path('views/admin/category'),
            __DIR__ . '/../config/category.php' => config_path('constants/admin/category.php'),
            __DIR__ . '/../src/Controllers' => app_path('Http/Controllers/Admin/CategoryManager'),
            __DIR__ . '/../src/Models' => app_path('Models/Admin/Category'),
            __DIR__ . '/routes/web.php' => base_path('routes/admin/category.php'),
        ], 'category');


    }

    protected function registerAdminRoutes()
    {
        if (!Schema::hasTable('admins')) {
            return; // Avoid errors before migration
        }

        $admin = DB::table('admins')
            ->orderBy('created_at', 'asc')
            ->first();
            
        $slug = $admin->website_slug ?? 'admin';


        Route::middleware('web')
            ->prefix("{$slug}/admin") // dynamic prefix
            ->group(function () {
                $this->loadRoutesFrom(__DIR__.'/routes/web.php');
            });
    }

    public function register()
    {
        // You can bind classes or configs here
    }
}
