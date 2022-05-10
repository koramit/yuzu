<?php

namespace App;

use App\Models\Visit;
use App\Traits\OPDCardExportable;
use Rap2hpoutre\FastExcel\Facades\FastExcel;

class ExcelOnDemand
{
    use OPDCardExportable;

    public function export()
    {
        $data = $this->kantarida2202();

        return FastExcel::data(collect($data))->export(storage_path('app/temp/kk.xlsx'));
    }

    protected function kantarida2202()
    {
        $visits = Visit::with('patient')
                        ->with(['vaccinations' => fn ($q) => $q->select(['vaccinated_at', 'brand_id', 'dose_no', 'patient_id'])->orderBy('dose_no')])
                        ->where('date_visit', '<', '2022-01-01')
                        ->where('patient_type', 2) // จนท
                        ->where('swabbed', true)
                        ->where('status', 4)
                        ->get();

        return $visits->transform(function ($v) {
            $visit = $this->allData($v);

            return [
                'name' => $visit['name'],
                'HN' => $visit['HN'],
                'gender' => $visit['gender'],
                'age' => $visit['age'],
                'date_of_visit' => $visit['date_visit'],
                'sap_id' => $visit['sap_id'],
                'position' => $visit['position'],
                'division' => $visit['division'],
                'service_location' => $visit['service_location'],
                'menstruation' => $visit['menstruation'],
                'latest_date_of_exposure' => $visit['date_latest_expose'],
                'temperature_celsius' => $visit['temperature_celsius'],
                // date 0f swab
                'symptoms' => $visit['symptoms'],
                'fever' => $visit['fever'],
                'cough' => $visit['cough'],
                'sore_throat' => $visit['sore_throat'],
                'rhinorrhoea' => $visit['rhinorrhoea'],
                'sputum' => $visit['sputum'],
                'fatigue' => $visit['fatigue'],
                'anosmia' => $visit['anosmia'],
                'loss_of_taste' => $visit['loss_of_taste'],
                'myalgia' => $visit['myalgia'],
                'diarrhea' => $visit['diarrhea'],
                'other_symptoms' => $visit['other_symptoms'],
                'onset_of_symtom' => $visit['onset'],

                'exposure_evaluation' => $visit['exposure_evaluation'],
                // 'date_latest_expose' => $visit['date_latest_expose'],
                'contact' => $visit['contact'],
                'contact_type' => $visit['contact_type'],
                'contact_detail' => $visit['contact_detail'],
                'hot_spot' => $visit['hot_spot'],
                'hot_spot_detail' => $visit['hot_spot_detail'],
                'other_detail' => $visit['other_detail'],

                'no_comorbids' => $visit['no_comorbids'],
                'dm' => $visit['dm'],
                'ht' => $visit['ht'],
                'dlp' => $visit['dlp'],
                'obesity' => $visit['obesity'],

                'weight' => $visit['weight'],

                'unvaccinated' => $visit['unvaccinated'],
                'Sinovac' => $visit['Sinovac'],
                'Sinopharm' => $visit['Sinopharm'],
                'AstraZeneca' => $visit['AstraZeneca'],
                'Moderna' => $visit['Moderna'],
                'Pfizer' => $visit['Pfizer'],
                'number_of_vaccination_dose' => $visit['doses'],
                'date_of_latest_vaccination' => $visit['date_latest_vacciniated'],

                'np_swab_result' => $visit['np_swab_result'],
                'ct_value' => $visit['np_swab_result_note'],
            ];
        });
    }
}
