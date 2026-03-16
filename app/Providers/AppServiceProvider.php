<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Validator;
use App\Rules\CurrentPassword;
use Illuminate\Support\Facades\URL;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        if (str_contains(request()->getHttpHost(), 'ngrok-free.app') || 
        str_contains(request()->getHttpHost(), 'ngrok-free.dev')) {
        URL::forceScheme('https');
    }
        // Register custom validation rule
        Validator::extend('current_password', function ($attribute, $value, $parameters, $validator) {
            return (new CurrentPassword())->passes($attribute, $value);
        }, (new CurrentPassword())->message());
    }

}