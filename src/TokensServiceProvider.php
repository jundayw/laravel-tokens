<?php

namespace Jundayw\LaravelTokens;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\ServiceProvider;

class TokensServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $this->mergeConfigFrom(
            __DIR__ . '/../config/tokens.php',
            'tokens'
        );
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__ . '/../config/tokens.php' => config_path('tokens.php'),
            ], 'tokens-config');
            $this->publishes([
                __DIR__ . '/../database/migrations' => database_path('migrations'),
            ], 'tokens-migrations');
        }
        Auth::extend('tokens', function ($app, $name, $config) {
            $guard = new TokensGuard($app, $name, $config);
            if (method_exists($guard, 'setRequest')) {
                $guard->setRequest($app->refresh('request', $guard, 'setRequest'));
            }
            return $guard;
        });
    }
}
