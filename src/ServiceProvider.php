<?php

namespace EloquentSessionHandler;

use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\ServiceProvider as LaravelServiceProvider;

class ServiceProvider extends LaravelServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        Session::extend('eloquent', function () {
            if (!is_string($model = Config::get('session.models.session', \EloquentSessionHandler\Session::class))) {
                throw new \RuntimeException('[session.models.session] should be a string');
            }

            if (!is_int($minutes = Config::get('session.lifetime', 120))) {
                throw new \RuntimeException('[session.lifetime] should be a integer');
            }

            return new Handler($model, $minutes);
        });
    }
}
