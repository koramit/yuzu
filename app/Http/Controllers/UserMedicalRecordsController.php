<?php

namespace App\Http\Controllers;

use App\Models\Visit;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Request;
use Inertia\Inertia;

class UserMedicalRecordsController extends Controller
{
    public function __invoke()
    {
        $user = Auth::user();

        if (!$user->patient_linked || !$user->line_active) {
            if (!$user->patient_linked) {
                $messages[] = 'กรุณาทำการยืนยัน HN';
            }
            if (!$user->line_active) {
                $messages[] = 'กรุณาทำการตั้งค่าการแจ้งเตือน';
            }

            return Redirect::route('preferences')->with('messages', [
                'status' => 'info',
                'messages' => $messages
            ]);
        }

        Request::session()->flash('page-title', 'ประวัติการตรวจของฉัน');
        Request::session()->flash('main-menu-links', [
            ['icon' => 'home', 'label' => 'หน้าหลัก', 'route' => 'home', 'can' => true],
        ]);

        // $visits = Visit::whereIn('id', [2500, 2750, 3000, 3250, 3505])
        $visits = Visit::wherePatientId($user->profile['patient_id'])
                        ->whereStatus(4) // discharged
                        ->orderByDesc('date_visit')
                        ->get()
                        ->transform(function ($visit) {
                            return [
                                'slug' => $visit->slug,
                                'date_visit' => $visit->date_visit->format('d M Y'),
                                'swabbed' => $visit->swabbed,
                                'result' => $visit->form['management']['np_swab_result'] ?? 'รอผล',
                                'note' => $visit->form['management']['np_swab_result_note'] ?? '',
                                'screenshot' => $visit->form['management']['screenshot'] ?? null,
                            ];
                        });

        return Inertia::render('Auth/MedicalRecords', ['visits' => $visits]);
    }
}
