<?php

namespace App\Http\Controllers;

use App\Events\VisitUpdated;
use App\Models\Visit;
use Illuminate\Support\Facades\Auth;

class VisitAuthorizationController extends Controller
{
    public function store(Visit $visit)
    {
        $visit->authorized_at = now();
        $visit->save();
        $visit->actions()->create([
            'action' => 'authorize',
            'visit_id' => $visit->id,
            'user_id' => Auth::id(),
        ]);
        VisitUpdated::dispatch($visit);

        return redirect()->back();
    }
}
