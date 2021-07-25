<?php

namespace App\Http\Controllers;

use App\Models\Visit;
use Rap2hpoutre\FastExcel\Facades\FastExcel;

class VisitExportController extends Controller
{
    public function __invoke()
    {
        $visits = Visit::with('patient')
                       ->whereIn('status', [3, 4])
                       ->get()
                       ->transform(function ($visit) {
                           return [
                                'id' => $visit->id,
                                'HN' => $visit->hn,
                                'name' => $visit->patient_name,
                                'result' => $visit->form['result'] ?? null,
                            ];
                       });

        $filename = 'รายงานผู้ป่วย ARI Clinic.xlsx';

        return FastExcel::data($visits)->download($filename);
    }
}
