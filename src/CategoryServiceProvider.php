<?php

namespace admin\categories;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

class CategoryServiceProvider extends ServiceProvider
{
    public function boot()
    {
        // Load routes, views, migrations from the package  
        $this->loadViewsFrom([
            base_path('Modules/Categories/resources/views'), // Published module views first
            resource_path('views/admin/category'), // Published views second
            __DIR__ . '/../resources/views'      // Package views as fallback
        ], 'category');
        
        // Also register module views with a specific namespace for explicit usage
        if (is_dir(base_path('Modules/Categories/resources/views'))) {
            $this->loadViewsFrom(base_path('Modules/Categories/resources/views'), 'categories-module');
        }
        $this->loadMigrationsFrom(__DIR__ . '/../database/migrations');
        // Also load migrations from published module if they exist
        if (is_dir(base_path('Modules/Categories/database/migrations'))) {
            $this->loadMigrationsFrom(base_path('Modules/Categories/database/migrations'));
        }
        $this->mergeConfigFrom(__DIR__ . '/../config/category.php', 'category.config');
        // Also merge config from published module if it exists
        if (file_exists(base_path('Modules/Categories/config/categories.php'))) {
            $this->mergeConfigFrom(base_path('Modules/Categories/config/categories.php'), 'category.config');
        }
        
        // Only publish automatically during package installation, not on every request
        // Use 'php artisan categories:publish' command for manual publishing
        // $this->publishWithNamespaceTransformation();
        
        // Standard publishing for non-PHP files
        $this->publishes([
            __DIR__ . '/../config/' => base_path('Modules/Categories/config/'),
            __DIR__ . '/../database/migrations' => base_path('Modules/Categories/database/migrations'),
            __DIR__ . '/../resources/views' => base_path('Modules/Categories/resources/views/'),
        ], 'category');
       
        $this->registerAdminRoutes();
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
                // Load routes from published module first, then fallback to package
                if (file_exists(base_path('Modules/Categories/routes/web.php'))) {
                    $this->loadRoutesFrom(base_path('Modules/Categories/routes/web.php'));
                } else {
                    $this->loadRoutesFrom(__DIR__ . '/routes/web.php');
                }
            });
    }

    public function register()
    {
        // Register the publish command
        if ($this->app->runningInConsole()) {
            $this->commands([
                \admin\categories\Console\Commands\PublishCategoriesModuleCommand::class,
                \admin\categories\Console\Commands\CheckModuleStatusCommand::class,
                \admin\categories\Console\Commands\DebugCategoriesCommand::class,
            ]);
        }
    }

    /**
     * Publish files with namespace transformation
     */
    protected function publishWithNamespaceTransformation()
    {
        // Define the files that need namespace transformation
        $filesWithNamespaces = [
            // Controllers
            __DIR__ . '/../src/Controllers/CategoryManagerController.php' => base_path('Modules/Categories/app/Http/Controllers/Admin/CategoryManagerController.php'),
            
            // Models
            __DIR__ . '/../src/Models/Category.php' => base_path('Modules/Categories/app/Models/Category.php'),
            
            // Requests
            __DIR__ . '/../src/Requests/CategoryCreateRequest.php' => base_path('Modules/Categories/app/Http/Requests/CategoryCreateRequest.php'),
            __DIR__ . '/../src/Requests/CategoryUpdateRequest.php' => base_path('Modules/Categories/app/Http/Requests/CategoryUpdateRequest.php'),
            
            // Routes
            __DIR__ . '/routes/web.php' => base_path('Modules/Categories/routes/web.php'),
        ];

        foreach ($filesWithNamespaces as $source => $destination) {
            if (File::exists($source)) {
                // Create destination directory if it doesn't exist
                File::ensureDirectoryExists(dirname($destination));
                
                // Read the source file
                $content = File::get($source);
                
                // Transform namespaces based on file type
                $content = $this->transformNamespaces($content, $source);
                
                // Write the transformed content to destination
                File::put($destination, $content);
            }
        }
    }

    /**
     * Transform namespaces in PHP files
     */
    protected function transformNamespaces($content, $sourceFile)
    {
        // Define namespace mappings
        $namespaceTransforms = [
            // Main namespace transformations
            'namespace admin\\categories\\Controllers;' => 'namespace Modules\\Categories\\app\\Http\\Controllers\\Admin;',
            'namespace admin\\categories\\Models;' => 'namespace Modules\\Categories\\app\\Models;',
            'namespace admin\\categories\\Requests;' => 'namespace Modules\\Categories\\app\\Http\\Requests;',
            
            // Use statements transformations
            'use admin\\categories\\Controllers\\' => 'use Modules\\Categories\\app\\Http\\Controllers\\Admin\\',
            'use admin\\categories\\Models\\' => 'use Modules\\Categories\\app\\Models\\',
            'use admin\\categories\\Requests\\' => 'use Modules\\Categories\\app\\Http\\Requests\\',
            
            // Class references in routes
            'admin\\categories\\Controllers\\CategoryManagerController' => 'Modules\\Categories\\app\\Http\\Controllers\\Admin\\CategoryManagerController',
        ];

        // Apply transformations
        foreach ($namespaceTransforms as $search => $replace) {
            $content = str_replace($search, $replace, $content);
        }

        // Handle specific file types
        if (str_contains($sourceFile, 'Controllers')) {
            $content = $this->transformControllerNamespaces($content);
        } elseif (str_contains($sourceFile, 'Models')) {
            $content = $this->transformModelNamespaces($content);
        } elseif (str_contains($sourceFile, 'Requests')) {
            $content = $this->transformRequestNamespaces($content);
        } elseif (str_contains($sourceFile, 'routes')) {
            $content = $this->transformRouteNamespaces($content);
        }

        return $content;
    }

    /**
     * Transform controller-specific namespaces
     */
    protected function transformControllerNamespaces($content)
    {
        // Update use statements for models and requests
        $content = str_replace(
            'use admin\\categories\\Models\\Category;',
            'use Modules\\Categories\\app\\Models\\Category;',
            $content
        );
        
        $content = str_replace(
            'use admin\\categories\\Requests\\CategoryCreateRequest;',
            'use Modules\\Categories\\app\\Http\\Requests\\CategoryCreateRequest;',
            $content
        );
        
        $content = str_replace(
            'use admin\\categories\\Requests\\CategoryUpdateRequest;',
            'use Modules\\Categories\\app\\Http\\Requests\\CategoryUpdateRequest;',
            $content
        );

        return $content;
    }

    /**
     * Transform model-specific namespaces
     */
    protected function transformModelNamespaces($content)
    {
        // Any model-specific transformations
        return $content;
    }

    /**
     * Transform request-specific namespaces
     */
    protected function transformRequestNamespaces($content)
    {
        // Any request-specific transformations
        return $content;
    }

    /**
     * Transform route-specific namespaces
     */
    protected function transformRouteNamespaces($content)
    {
        // Update controller references in routes
        $content = str_replace(
            'admin\\categories\\Controllers\\CategoryManagerController',
            'Modules\\Categories\\app\\Http\\Controllers\\Admin\\CategoryManagerController',
            $content
        );

        return $content;
    }
}
