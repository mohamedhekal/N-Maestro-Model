<?php

namespace Noouh\AutoModelFillable\Providers;

use Illuminate\Support\ServiceProvider;
use Noouh\AutoModelFillable\Console\Commands\GenerateFillable;

class NoouhAutoModelFillableServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        if ($this->app->runningInConsole()) {
            $this->commands([
                GenerateFillable::class,
            ]);
        }
    }
}
