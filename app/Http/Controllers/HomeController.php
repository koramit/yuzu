<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Redirect;

class HomeController extends Controller
{
    public function __invoke()
    {
        return Redirect::route('visits');
    }
}
