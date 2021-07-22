<?php

namespace App\Http\Controllers;

use App\Models\Visit;
use Illuminate\Support\Facades\Auth;

class VisitAttachOPDCardController extends Controller
{
    public function store(Visit $visit)
    {
        $visit->attached_opd_card_at = now();
        $visit->save();
        $visit->actions()->create([
            'action' => 'attach_opd_card',
            'visit_id' => $visit->id,
            'user_id' => Auth::id(),
        ]);

        return redirect()->back();
    }
}
