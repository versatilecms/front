<?php

namespace Versatile\Front\Providers;

use Illuminate\Http\Request;
use Versatile\Front\Commands;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\ServiceProvider;
use Laravel\Scout\Console\ImportCommand;
use Illuminate\Console\Scheduling\Schedule;

class VersatileFrontendServiceProvider extends ServiceProvider
{
    /**
     * Our root directory for this package to make traversal easier
     */
    protected $packagePath = __DIR__ . '/../../';

    /**
     * Bootstrap the application services
     *
     * @param Request $request
     *
     * @return void
     */
    public function boot()
    {
        $this->strapEvents();
        $this->strapRoutes();
        $this->strapHelpers();
        $this->strapCommands();
    }

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {
        $this->mergeConfigFrom($this->packagePath . 'config/versatile-frontend.php', 'versatile-frontend');

        // Merge our Scout config over
        $this->mergeConfigFrom($this->packagePath . 'config/scout.php', 'scout');

        $this->app->alias(VersatileFrontend::class, 'versatile-frontend');

        if ($this->app->runningInConsole()) {
            $this->strapPublishers();
        }
    }

    /**
     * Register the publishable files.
     */
    private function strapPublishers()
    {
        $publishable = [
            'config' => [
                $this->packagePath . 'config/versatile-frontend.php' => config_path('versatile-frontend.php'),
            ]
        ];

        foreach ($publishable as $group => $paths) {
            $this->publishes($paths, $group);
        }
    }

    /**
     * Bootstrap our Events
     */
    protected function strapEvents()
    {
        // When an Eloquent Model is updated, re-generate our indices (could get intense)
        Event::listen(['eloquent.saved: *', 'eloquent.deleted: *'], function () {
            Artisan::call("versatile-frontend:generate-search-indices");
        });
    }

    /**
     * Bootstrap our Routes
     */
    protected function strapRoutes()
    {
        // Pull default web routes
        $this->loadRoutesFrom(base_path('/routes/web.php'));

        // Then add our Pages and Posts Routes
        $this->loadRoutesFrom($this->packagePath . 'routes/web.php');
    }

    /**
     * Load helpers.
     */
    protected function strapHelpers()
    {
        require_once $this->packagePath . '/src/Helpers/ImageResize.php';
    }

    /**
     * Bootstrap our Commands/Schedules
     */
    protected function strapCommands()
    {
        if ($this->app->runningInConsole()) {
            $this->commands([
                Commands\InstallCommand::class,
                Commands\ThumbnailsClean::class
            ]);
        }

        // Register our commands
        $this->commands([
            ImportCommand::class,
            Commands\GenerateSitemap::class,
            Commands\GenerateSearchIndices::class
        ]);

        // Schedule our commands
        $this->app->booted(function () {
            $schedule = $this->app->make(Schedule::class);

            $schedule->command('versatile-frontend:clean-thumbnails')->dailyAt('13:00');
            $schedule->command('versatile-frontend:generate-sitemap')->dailyAt('13:15');
            $schedule->command('versatile-frontend:generate-search-indices')->dailyAt('13:30');
        });
    }
}
