<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class InTransitController extends Controller
{
    public function __invoke()
    {
        $user = Auth::user();

        return Inertia::render('InTransit', [
            'configs' => [
                'on_duty_link' => route($user->home_page),
                'on_exam_link' => $user->line_active && $user->patient_linked ? route('medical-records') : route('preferences'),
            ]
        ]);
    }
}
