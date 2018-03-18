<?php

namespace Hiren\Autocrud;

use Illuminate\Support\ServiceProvider;

class AutocrudServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot()
    {
        //route
        include __DIR__.'/routes.php';
        
        //load views
        $this->loadViewsFrom(__DIR__.'/views', 'autocrud');

        //copy js, css, img to public directory
        $this->publishes([
            __DIR__.'/autocrud' => public_path('autocrud'),
            __DIR__.'/models' => public_path('../app'),
        ], 'public');

        //add migration files 
        $this->loadMigrationsFrom(__DIR__.'/migrations');
    }

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {
    
    }
}
