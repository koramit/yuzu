<?php

namespace App\Http\Controllers;

use App\Contracts\PatientAPI;
use App\Events\VisitUpdated;
use App\Managers\PatientManager;
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
        $visit->actions()->create(['action' => 'print', 'user_id' => Auth::id()]);

        if (! $visit->attached_opd_card_at) {
            $visit->attached_opd_card_at = now();
            $visit->save();
            VisitUpdated::dispatch($visit);
        }

        $manager = new PatientManager();
        $patient = $manager->manage($visit->hn, true);
        if ($patient['found'] && $visit->patient_name !== $patient['patient']->full_name) {
            $visit->patient_name = $patient['patient']->full_name;
            $visit->save();
        }

        return Inertia::render('Printouts/OPDCard', [
            'content' => (new VisitManager())->getPrintConent($visit),
            'configs' => [
                'left_topics' => ['อาการแสดง', 'ATK', 'ประวัติอื่น', 'ประวัติเสี่ยง', 'โรคประจำตัว', 'ประวัติการฉีดวัคซีน COVID-19'],
                'right_topics' => ['วินิจฉัย', 'การจัดการ', 'คำแนะนำสำหรับผู้ป่วย', 'note'],
            ],
        ]);
    }
}
