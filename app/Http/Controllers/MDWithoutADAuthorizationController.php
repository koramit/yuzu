<?php

namespace App\Http\Controllers;

use App\Managers\VisitManager;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;

class MDWithoutADAuthorizationController extends Controller
{
    public function index(Request $request)
    {
        $manager = new VisitManager;
        $user = $request->user();
        $flash = $manager->getFlash($user);
        $flash['page-title'] = 'แพทย์ไม่มีรหัส SIIT';
        $manager->setFlash($flash);

        $users = User::query()
            ->with('roles')
            ->where('login', 'like', '%@%')
            ->get()
            ->transform(fn ($u) => [
                'full_name' => $u->profile['full_name'],
                'login' => $u->login,
                'md' => $u->roles->where('name', 'md')->count() > 0,
                'update_route' => route('authorize-md-without-ad.update', $u->login),
            ]);

        return Inertia::render('Auth/AuthorizeUser', ['users' => $users]);
    }

    /**
     * @throws \Exception
     */
    public function update(User $user, Request $request)
    {
        $md = Role::query()->where('name', 'md')->first();
        $user->roles()->toggle($md->id);
        cache()->forget("uid-{$user->id}-abilities");
        cache()->forget("uid-{$user->id}-role-names");
        if ($user->roles()->where('name', 'md')->count()) { // authorize
            $homePage = 'visits.screen-list';
        } else { // revoke
            $homePage = 'preferences';
        }
        $profile = $user->profile;
        $profile['authority'] = $request->user()->login;
        $user->update([
            'home_page' => $homePage,
            'profile' => $profile,
        ]);

        return ['ok' => true];
    }
}
