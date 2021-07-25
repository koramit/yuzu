<?php

namespace App\Http\Controllers;

use App\Models\Visit;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Request;

class VisitEvaluateController extends Controller
{
    public function __invoke(Visit $visit)
    {
        $visit->forceFill([
            'form->evaluation->consultation' => Request::input('consultation'),
        ]);
        $visit->save();
        $visit->actions()->create(['action' => 'update', 'user_id' => Auth::id()]);

        return Redirect::route('visits');
    }
}
