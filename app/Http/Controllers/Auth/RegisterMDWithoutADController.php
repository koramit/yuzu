<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Redirect;
use Inertia\Inertia;

class RegisterMDWithoutADController extends Controller
{
    public function create()
    {
        return Inertia::render('Auth/RegisterDoctor');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'login' => 'required|email|unique:users',
            'name' => 'required|string|unique:users',
            'full_name' => 'required|string',
            'password' => 'required|confirmed:password_confirmation',
            'password_confirmation' => 'required|string',
            'tel_no' => 'required|digits_between:9,10',
            'pln' => 'required|digits:5',
            'agreement_accepted' => 'required|bool',
        ]);

        $user = new User();
        $user->login = $validated['login'];
        $user->name = $validated['name'];
        $user->home_page = 'preferences';
        $user->password = Hash::make($validated['password']);
        $user->profile = [
            'full_name' => $validated['full_name'],
            'tel_no' => $validated['tel_no'],
            'org_id' => null,
            'division' => null,
            'position' => null,
            'pln' => $validated['pln'],
            'remark' => null,
        ];
        $user->save();

        Auth::login($user);

        return Redirect::route($user->home_page);
    }
}
