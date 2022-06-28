<?php

namespace App\Http\Controllers;

use App\Managers\MocktailManager;
use App\Models\LoadDataRecord;
use App\Models\Visit;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Rap2hpoutre\FastExcel\Facades\FastExcel;

class PositiveCaseDecisionExportController extends Controller
{
    public function __invoke()
    {
        $dateVisit = Session::get('positive-decision-export-date', now('asia/bangkok')->format('Y-m-d'));
        $manager = new MocktailManager();
        $user = Auth::user();
        $positiveCases = Visit::with('patient')
                              ->where('swabbed', true)
                              ->whereDateVisit($dateVisit)
                              ->where('form->management->np_swab_result', 'Detected')
                              ->get()
                              ->transform(function ($visit) use ($manager) {
                                  $positive = $manager->getReferCase($visit);

                                  return [
                                      'Name' => $positive['patient_name'],
                                      'Age' => $positive['age'],
                                      'HN' => $positive['hn'],
                                      'Tel' => trim($positive['tel_no'].' '.$positive['tel_no_alt'] ?? ''),
                                      'Type' => $positive['patient_type'],
                                      'Insurance' => $positive['insuranceShow'],
                                      'National_Id' => $positive['document_id'],
                                      'U/D' => $positive['ud'],
                                      'Symptom' => $positive['symptom'],
                                      'Onset' => $positive['onset'],
                                      'O2 sat' => $positive['o2_sat'],
                                      'Weight' => $positive['weight'],
                                      'CT' => $positive['lab_remark'] ? str_replace("\n", ' ', $positive['lab_remark']) : null,
                                      'Note' => $positive['note'] ? str_replace("\n", ' ', $positive['note']) : null,
                                      'Remark' => $positive['remark'] ? str_replace("\n", ' ', $positive['remark']) : null,
                                      'Decision' => $positive['refer_to'],
                                  ];
                              });

        $filename = 'ARI Positive Case Decision@'.$dateVisit.'.xlsx';
        LoadDataRecord::create([
            'export' => true,
            'configs' => [
                'date_visit' => $dateVisit,
                'data' => 'positive_case_decision',
            ],
            'user_id' => $user->id,
        ]);

        return FastExcel::data($positiveCases)->download($filename);
    }
}
