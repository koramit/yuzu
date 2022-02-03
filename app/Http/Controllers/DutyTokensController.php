<?php

namespace App\Http\Controllers;

use App\Managers\VisitManager;
use App\Models\DutyToken;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Inertia\Inertia;

class DutyTokensController extends Controller
{
    public function show()
    {
        $manager = new VisitManager;
        $user = Auth::user();
        $flash = $manager->getFlash($user);
        $flash['page-title'] = 'รหัสปฏิบัติงาน';
        $manager->setFlash($flash);

        return Inertia::render('Auth/ActiveDutyToken', ['activeDutyToken' => DutyToken::latest()->first()?->token ?? '']);
    }

    public function store()
    {
        DutyToken::generate(Auth::user());

        return Redirect::route('duty-token.show');
    }
}
