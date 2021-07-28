<?php

namespace App\Http\Controllers;

use App\Models\Visit;
use Illuminate\Support\Carbon;
use Rap2hpoutre\FastExcel\Facades\FastExcel;

class OPDCardExportController extends Controller
{
    public function __invoke()
    {
        $visits = Visit::with('patient')
                       ->whereIn('status', [3, 4])
                       ->get()
                       ->transform(function ($visit) {
                           return [
                                'id' => $visit->id,
                                'date_visit' => $visit->date_visit->format('d-M-Y'),
                                'HN' => $visit->hn,
                                'name' => $visit->patient_name,
                                'patient_type' => $visit->patient_type,
                                'screen_type' => $visit->screen_type,
                                'symptoms' => $visit->form['symptoms']['asymptomatic_symptom'] ? 'ไม่มีอาการ' : 'มีอาการ',
                                'onset' => $visit->form['symptoms']['date_symptom_start'] ? Carbon::create($visit->form['symptoms']['date_symptom_start'])->format('d-M-Y') : null,
                                'fever' => $visit->form['symptoms']['fever'] ? 'YES' : 'NO',
                                'cough' => $visit->form['symptoms']['cough'] ? 'YES' : 'NO',
                                'sore_throat' => $visit->form['symptoms']['sore_throat'] ? 'YES' : 'NO',
                                'rhinorrhoea' => $visit->form['symptoms']['rhinorrhoea'] ? 'YES' : 'NO',
                                'sputum' => $visit->form['symptoms']['sputum'] ? 'YES' : 'NO',
                                'fatigue' => $visit->form['symptoms']['fatigue'] ? 'YES' : 'NO',
                                'anosmia' => $visit->form['symptoms']['anosmia'] ? 'YES' : 'NO',
                                'loss_of_taste' => $visit->form['symptoms']['loss_of_taste'] ? 'YES' : 'NO',
                                'diarrhea' => $visit->form['symptoms']['diarrhea'] ? 'YES' : 'NO',
                                'other_symptoms' => $visit->form['symptoms']['other_symptoms'] ? 'YES' : 'NO',
                                'np_swab' => $visit->form['management']['np_swab'] ? 'YES' : 'NO',
                                'np_swab_result' => $visit->form['management']['np_swab'] ? ($visit->form['result'] ?? 'pending') : null,
                            ];
                       });

        $filename = 'รายงานผู้ป่วย ARI Clinic.xlsx';

        return FastExcel::data($visits)->download($filename);
    }
}
