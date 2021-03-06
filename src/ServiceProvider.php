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
            /** @var string */
            $model = Config::get('session.model', \EloquentSessionHandler\Session::class);

            /** @var int */
            $minutes = Config::get('session.lifetime', 120);

            return new Handler($model, $minutes);
        });
    }
}
