<?php

namespace LaravelEloquentSessionHandler;

use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Session as LaravelSession;
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
        LaravelSession::extend('eloquent', function () {
            return new EloquentSessionHandler(
                Session::class,
                Config::get('session.lifetime', 120)
            );
        });
    }
}
