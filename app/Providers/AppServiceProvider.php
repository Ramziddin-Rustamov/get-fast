<?php

namespace App\Providers;

use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Gate;
use App\Models\User;
use Illuminate\Http\Resources\Json\JsonResource;

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

        Gate::define('admin', function (User $user) {
            return $user->role === 'admin' || $user->email == "rustamovvramziddin@gmail.com";
        });

        Gate::define('client_web', function (User $user) {
            return $user->role === 'client';
        });

        Gate::define('driver_web', function (User $user) {
            return $user->role === 'driver';
        });
        JsonResource::withoutWrapping(); // tried both
    }
}
