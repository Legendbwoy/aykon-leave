<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Validator;
use App\Rules\CurrentPassword;

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
        // Register custom validation rule
        Validator::extend('current_password', function ($attribute, $value, $parameters, $validator) {
            return (new CurrentPassword())->passes($attribute, $value);
        }, (new CurrentPassword())->message());
    }
}