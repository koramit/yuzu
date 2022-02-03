<?php

namespace App\Http\Controllers;

use App\Models\DutyToken;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Request;
use Inertia\Inertia;

class DutyTokenUserAuthorizationController extends Controller
{
    public function create()
    {
        return Inertia::render('Auth/Authorize');
    }

    public function store()
    {
        $activeToken = DutyToken::latest()->first()?->token;
        if (!$activeToken || Request::input('token', null) !== $activeToken) {
            return Redirect::back()->withErrors(['token' => 'รหัสไม่ถูกต้อง']);
        }
        $user = Auth::user();
        $user->duty_token = $activeToken;

        return Redirect::route($user->home_page);
    }
}
