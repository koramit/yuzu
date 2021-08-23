<?php

namespace App\Http\Controllers\Auth;

use App\Contracts\AuthenticationAPI;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\Session;
use Inertia\Inertia;

class AuthenticatedSessionController extends Controller
{
    /**
     * Display the login view.
     *
     * @return \Inertia\Respons
     */
    public function create()
    {
        return Inertia::render('Auth/Login');
    }

    public function store(AuthenticationAPI $api)
    {
        Request::validate([
            'login' => 'required|string',
            'password' => 'required|string',
        ]);

        $data = $api->authenticate(Request::input('login'), Request::input('password'));

        if (! $data['found']) {
            return back()->withErrors([
                'login' => $data['message'],
            ]);
        }

        $user = User::whereLogin(Request::input('login'))->first();
        if ($user = User::whereLogin(Request::input('login'))->first()) {
            Auth::login($user);

            Cache::forget("uid-{$user->id}-role-names");
            Cache::forget("uid-{$user->id}-abilities");

            return Redirect::intended(route($user->home_page));
        }

        Session::put('profile', $data);

        return Redirect::route('register');
    }

    public function update()
    {
        return ['active' => true];
    }

    public function destroy()
    {
        Auth::guard('web')->logout();

        Request::session()->invalidate();

        Request::session()->regenerateToken();

        return Redirect::route('login');
    }
}
