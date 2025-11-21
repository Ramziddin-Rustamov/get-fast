<?php

namespace App\Providers;

use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Gate;
use App\Models\User;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Request;

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

        JsonResource::withoutWrapping(); // tried both

        $lang = Request::header('Accept-Language') ?? Request::get('lang') ?? 'en';
        if (in_array($lang, ['en', 'uz', 'ru'])) {
            App::setLocale($lang);
        }
    }
}
