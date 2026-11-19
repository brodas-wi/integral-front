<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;

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
        RateLimiter::for('form-submissions', fn (Request $request) => [
            Limit::perMinute(5)->by('minute:' . $request->ip()),
            Limit::perHour(30)->by('hour:' . $request->ip()),
            Limit::perHour(300)->by('form:' . $request->route('slug')),
        ]);
    }
}