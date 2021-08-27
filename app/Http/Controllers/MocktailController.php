<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Request;

class MocktailController extends Controller
{
    public function __invoke()
    {
        $user = Auth::user();
        $data = [
            'login' => Request::input('login'),
            'password' => Request::input('password'),
            'org_id' => $user->profile['org_id'] ?? null,
        ];

        $response = Http::post(config('services.mocktail.link_user_endpoint'), $data);

        if ($response->json()['found'] ?? false) {
            $user->forceFill([
                'profile->mocktail_token' => Crypt::encryptString($response->json()['token']),
            ])->save();

            return ['ok' => true];
        }

        return [
            'ok' => false,
            'login' => $response->json()['login'] ?? null,
            'org_id' => $response->json()['org_id'] ?? null,
        ];
    }
}
