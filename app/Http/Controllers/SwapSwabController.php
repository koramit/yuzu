<?php

namespace App\Http\Controllers;

use App\Models\Visit;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;

class SwapSwabController extends Controller
{
    public function __invoke(Visit $visit)
    {
        $visit->swabbed = !$visit->swabbed;
        $visit->save();
        $visit->actions()->create(['action' => 'swap', 'user_id' => Auth::id()]);
        return Redirect::back();
    }
}
