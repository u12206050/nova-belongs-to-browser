<?php

namespace Day4\BelongsToBrowser;

use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use Laravel\Nova\Events\ServingNova;
use Laravel\Nova\Nova;

class FieldServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        Nova::serving(function (ServingNova $event) {
            Nova::script('belongs-to-browser', __DIR__.'/../dist/js/field.js');
            Nova::style('belongs-to-browser', __DIR__.'/../dist/css/field.css');
        });
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        if ($this->app->routesAreCached()) {
            return;
        }

        Route::middleware(['nova'])
            ->namespace('Day4\BelongsToBrowser\Http\Controllers')
            ->prefix('day4/belongs-to-browser')
            ->group(__DIR__.'/../routes/api.php');
    }
}
