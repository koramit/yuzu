<?php

namespace App\Http\Controllers;

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

        return redirect()->back();
    }

    public function destroy(Visit $visit)
    {
        $visit->authorized_at = null;
        $visit->actions()->create([
            'action' => 'unauthorize',
            'visit_id' => $visit->id,
            'user_id' => Auth::id(),
        ]);
    }
}
