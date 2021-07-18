<?php

namespace App\Http\Controllers;

use App\Models\Visit;
use Illuminate\Support\Facades\Request;
use Inertia\Inertia;

class VisitScreenQueueController extends Controller
{
    public function index()
    {
        Request::session()->flash('main-menu-links', [
            ['icon' => 'thermometer', 'label' => 'คัดกรอง', 'route' => 'visits.screen-queue'],
            ['icon' => 'stethoscope', 'label' => 'พบแพทย์', 'route' => 'visits.exam-queue'],
            ['icon' => 'virus', 'label' => 'Swab', 'route' => 'visits.swab-queue'],
        ]);

        Request::session()->flash('action-menu', [
            // ['icon' => 'clipboard-list', 'label' => 'รายการเคส', 'route' => 'refer-cases'],
            ['icon' => 'notes-medical', 'label' => 'เพิ่มเคสใหม่', 'action' => 'create-visit'],
        ]);

        $visits = Visit::with('patient')
                       ->whereStatus(1)
                       ->orderByDesc('updated_at')
                       ->paginate()
                       ->through(function ($visit) {
                           return [
                               'slug' => $visit->slug,
                               'hn' => $visit->patient->hn ?? null,
                               'patient_name' => $visit->patient_name,
                               'patient_type' => $visit->patient_type,
                               'date_visit' => $visit->date_visit->format('d M Y'),
                               'updated_at_for_humans' => $visit->updated_at_for_humans,
                           ];
                       });

        return Inertia::render('Visits/Index', ['visits' => $visits]);
    }
}
