<?php

namespace App\Http\Controllers;

use App\Managers\VisitManager;
use App\Models\Visit;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request;
use Inertia\Inertia;

class PrintOPDCardController extends Controller
{
    public function __invoke(Visit $visit)
    {
        Request::session()->flash('page-title', $visit->title);
        $visit->actions()->create(['action' => 'view', 'user_id' => Auth::id()]);

        return Inertia::render('Printouts/OPDCard', [
            'content' => (new VisitManager())->getPrintConent($visit),
            'configs' => [
                'left_topics' => ['อาการแสดง', 'ประวัติเสี่ยง', 'โรคประจำตัว', 'ประวัติการฉีดวัคซีน COVID-19'],
                'right_topics' => ['วินิจฉัย', 'การจัดการ', 'คำแนะนำสำหรับผู้ป่วย', 'note'],
            ],
        ]);
    }
}
