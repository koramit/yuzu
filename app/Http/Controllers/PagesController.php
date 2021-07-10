<?php

namespace App\Http\Controllers;

use Inertia\Inertia;

class PagesController extends Controller
{
    public function terms()
    {
        return Inertia::render('TermsAndPolicies/Revision1');
    }
}
