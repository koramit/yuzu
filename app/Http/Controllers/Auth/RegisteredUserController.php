<?php

namespace App\Http\Controllers\Auth;

use App\Events\Registered;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Str;
use Inertia\Inertia;

class RegisteredUserController extends Controller
{
    /**
     * Display the registration view.
     *
     * @return \Illuminate\View\View
     */
    public function create()
    {
        if ($profile = Session::get('profile', null)) {
            if (! isset($profile['is_md'])) {
                $profile['is_md'] = str_contains($profile['name'], 'พญ.') || str_contains($profile['name'], 'นพ.');
                Session::put('profile', $profile);
            }

            return Inertia::render('Auth/Register', ['profile' => $profile]);
        }

        return Redirect::route('login');
    }

    public function store()
    {
        Request::validate([
            'login' => 'required|string|unique:users',
            'name' => 'required|string|unique:users',
            'full_name' => 'required|string',
            'tel_no' => 'required|digits_between:9,10',
            'pln' => 'exclude_if:is_md,false|required|digits:5',
            'agreement_accepted' => 'required',
        ]);

        $data = Request::all();

        $profile = [
            'full_name' => $data['full_name'],
            'tel_no' => $data['tel_no'],
            'org_id' => $data['org_id'],
            'division' => $data['division'],
            'position' => $data['position'],
            'pln' => $data['pln'] ?? null,
            'remark' => $data['remark'],
        ];

        $user = new User();

        $user->login = $data['login'];
        $user->name = $data['name'];
        $user->home_page = 'preferences';
        $user->password = Hash::make(Str::random(64));
        $user->profile = $profile;
        $user->save();

        Registered::dispatch($user);

        Auth::login($user);

        $redirectTo = $user->home_page;
        if ($user->role_names->count() && !$user->role_names->intersect(config('app.specific_roles'))->count()) {
            $redirectTo = 'in-transit';
        }

        return Redirect::route($redirectTo);
    }
}
