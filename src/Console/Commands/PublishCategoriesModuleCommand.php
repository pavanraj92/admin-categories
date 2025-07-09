<?php

namespace admin\categories\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class PublishCategoriesModuleCommand extends Command
{
    protected $signature = 'categories:publish {--force : Force overwrite existing files}';
    protected $description = 'Publish Categories module files with proper namespace transformation';

    public function handle()
    {
        $this->info('Publishing Categories module files...');

        // Check if module directory exists
        $moduleDir = base_path('Modules/Categories');
        if (!File::exists($moduleDir)) {
            File::makeDirectory($moduleDir, 0755, true);
        }

        // Publish with namespace transformation
        $this->publishWithNamespaceTransformation();
        
        // Publish other files
        $this->call('vendor:publish', [
            '--tag' => 'category',
            '--force' => $this->option('force')
        ]);

        // Update composer autoload
        $this->updateComposerAutoload();

        $this->info('Categories module published successfully!');
        $this->info('Please run: composer dump-autoload');
    }

    protected function publishWithNamespaceTransformation()
    {
        $basePath = dirname(dirname(__DIR__)); // Go up to packages/admin/categories/src
        
        $filesWithNamespaces = [
            // Controllers
            $basePath . '/Controllers/CategoryManagerController.php' => base_path('Modules/Categories/app/Http/Controllers/Admin/CategoryManagerController.php'),
            
            // Models
            $basePath . '/Models/Category.php' => base_path('Modules/Categories/app/Models/Category.php'),
            
            // Requests
            $basePath . '/Requests/CategoryCreateRequest.php' => base_path('Modules/Categories/app/Http/Requests/CategoryCreateRequest.php'),
            $basePath . '/Requests/CategoryUpdateRequest.php' => base_path('Modules/Categories/app/Http/Requests/CategoryUpdateRequest.php'),
            
            // Routes
            $basePath . '/routes/web.php' => base_path('Modules/Categories/routes/web.php'),
        ];

        foreach ($filesWithNamespaces as $source => $destination) {
            if (File::exists($source)) {
                File::ensureDirectoryExists(dirname($destination));
                
                $content = File::get($source);
                $content = $this->transformNamespaces($content, $source);
                
                File::put($destination, $content);
                $this->info("Published: " . basename($destination));
            } else {
                $this->warn("Source file not found: " . $source);
            }
        }
    }

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
            $content = str_replace('use admin\\categories\\Models\\Category;', 'use Modules\\Categories\\app\\Models\\Category;', $content);
            $content = str_replace('use admin\\categories\\Requests\\CategoryCreateRequest;', 'use Modules\\Categories\\app\\Http\\Requests\\CategoryCreateRequest;', $content);
            $content = str_replace('use admin\\categories\\Requests\\CategoryUpdateRequest;', 'use Modules\\Categories\\app\\Http\\Requests\\CategoryUpdateRequest;', $content);
        }

        return $content;
    }

    protected function updateComposerAutoload()
    {
        $composerFile = base_path('composer.json');
        $composer = json_decode(File::get($composerFile), true);

        // Add module namespace to autoload
        if (!isset($composer['autoload']['psr-4']['Modules\\Categories\\'])) {
            $composer['autoload']['psr-4']['Modules\\Categories\\'] = 'Modules/Categories/app/';
            
            File::put($composerFile, json_encode($composer, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
            $this->info('Updated composer.json autoload');
        }
    }
}
