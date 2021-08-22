<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;

class HomeController extends Controller
{
    public function __invoke()
    {
        return Redirect::route(Auth::user()->home_page);
    }
}
