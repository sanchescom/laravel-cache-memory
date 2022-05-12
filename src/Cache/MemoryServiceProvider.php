<?php

namespace Sanchescom\Cache;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\ServiceProvider;

class MemoryServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->singleton('memory.block', function ($app) {
            return new MemoryBlock(
                $app['config']['cache.stores.memory.key'],
                $app['config']['cache.stores.memory.size']
            );
        });
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        Cache::extend('memory', function ($app) {
            return Cache::repository(new MemoryStore($app->make('memory.block')));
        });
    }
}
