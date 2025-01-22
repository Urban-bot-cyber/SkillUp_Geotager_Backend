<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Services\ImageUploadService;

class RepositoryServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        // Bind ImageUploadService to the service container
        $this->app->singleton(ImageUploadService::class, function ($app) {
            return new ImageUploadService();
        });
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        //
    }
}