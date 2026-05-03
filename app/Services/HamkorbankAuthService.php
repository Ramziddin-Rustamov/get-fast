<?php
namespace App\Services\V1;



class HamkorbankAuthService
{
    public static function getToken()
    {
        return Http::asForm()
            ->withBasicAuth(
                config('services.hamkorbank.key'),
                config('services.hamkorbank.secret')
            )
            ->post(config('services.hamkorbank.token_url'), [
                'grant_type' => 'client_credentials'
            ])
            ->json('access_token');
    }
}