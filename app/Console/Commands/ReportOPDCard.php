<?php

namespace App\Console\Commands;

use App\Models\Visit;
use App\Traits\OPDCardExportable;
use Illuminate\Console\Command;
use Rap2hpoutre\FastExcel\FastExcel;

class ReportOPDCard extends Command
{
    use OPDCardExportable;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'report-excel:opd-card {begin} {end}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'opd card to excel';


    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $begin = $this->argument('begin');
        $end = $this->argument('end');
        $filename = storage_path("app/temp/super-valentine-{$begin}-to-{$end}.xlsx");

        $visits = Visit::with('patient')
            // ->with(['vaccinations' => fn ($q) => $q->select(['vaccinated_at', 'brand_id', 'dose_no', 'patient_id'])->orderBy('dose_no')])
            ->where('swabbed', true)
            ->where('patient_type', 2)
            ->whereBetween('date_visit', [$begin, $end])
            ->where('status', 4)
            ->orderBy('discharged_at')
            ->get()
            ->transform(function ($visit) {
                return $this->transform($visit);
            });

        (new FastExcel($visits))->export($filename);

        return 0;
    }

    protected function transform(Visit $visit)
    {
        $form = $visit->form;
        $vaccinations = $visit->vaccinations()
            ->select(['vaccinated_at', 'brand_id', 'dose_no', 'patient_id'])
            ->where('vaccinated_at', '<', $visit->date_visit)
            ->orderBy('dose_no')
            ->get();

        return [
            'v_count' => $vaccinations->count(),
            'ชื่อ-นามสกุล' => $visit->patient_name, // 1
            'HN' => $visit->hn, // 2
            'เพศ' => $visit->patient->gender, // 3
            'อายุ' => $visit->age_at_visit, // 4
            'Date of visit' => $visit->date_visit->format('d M Y'), // 5
            'SAP ID' => $form['patient']['sap_id'], // 6
            'Position' => $form['patient']['position'], // 7
            'division' => $form['patient']['division'],
            'Service location' => $form['patient']['service_location'] ?? null, // 8
            'Menstruation' => $visit->menstruation, // 9
            'Latest date of exposure' => $this->castDate($form['exposure']['date_latest_expose']), // 10
            'temperature' => $form['patient']['temperature_celsius'], // 11
            'o2_sat' => $form['patient']['o2_sat'],
            'Date of swab' => $this->castDate($form['patient']['date_swabbed']),  // 12
            'Symptoms' => $form['symptoms']['asymptomatic_symptom'] ? 'ไม่มีอาการ' : 'มีอาการ',    // 13
            'fever' => $form['symptoms']['fever'] ? 'YES' : 'NO',
            'cough' => $form['symptoms']['cough'] ? 'YES' : 'NO',
            'sore throat' => $form['symptoms']['sore_throat'] ? 'YES' : 'NO',
            'rhinorrhoea' => $form['symptoms']['rhinorrhoea'] ? 'YES' : 'NO',
            'sputum' => $form['symptoms']['sputum'] ? 'YES' : 'NO',
            'fatigue' => $form['symptoms']['fatigue'] ? 'YES' : 'NO',
            'anosmia' => $form['symptoms']['anosmia'] ? 'YES' : 'NO',
            'loss of taste' => $form['symptoms']['loss_of_taste'] ? 'YES' : 'NO',
            'myalgia' => $form['symptoms']['myalgia'] ? 'YES' : 'NO',
            'diarrhea' => $form['symptoms']['diarrhea'] ? 'YES' : 'NO',
            'other_symptoms' => $form['symptoms']['other_symptoms'] ? 'YES' : 'NO',
            'Onset of symptom' => $form['symptoms']['date_symptom_start']                                  // 14
                ? \Carbon\Carbon::create($form['symptoms']['date_symptom_start'])->format('d-M-Y')
                : null,

            'contact' =>  $form['exposure']['contact'] ? 'YES' : 'NO',  // 15
            'contact_type' =>  $form['exposure']['contact_type'],
            'contact_detail' =>  $form['exposure']['contact_detail'] ? str_replace("\n", ' ', $form['exposure']['contact_detail']) : null,
            'hot_spot' =>  $form['exposure']['hot_spot'] ? 'YES' : 'NO',
            'hot_spot_detail' =>  $form['exposure']['hot_spot_detail'] ? str_replace("\n", ' ', $form['exposure']['hot_spot_detail']) : null,
            'other_detail' =>  $form['exposure']['other_detail'] ? str_replace("\n", ' ', $form['exposure']['other_detail']) : null,

            'No comorbids' => $form['comorbids']['no_comorbids'] ? 'YES' : 'NO',    // 16
            'dm' => $form['comorbids']['dm'] ? 'YES' : 'NO',
            'ht' => $form['comorbids']['ht'] ? 'YES' : 'NO',
            'dlp' => $form['comorbids']['dlp'] ? 'YES' : 'NO',
            'obesity' => $form['comorbids']['obesity'] ? 'YES' : 'NO',
            'other comorbids' => $form['comorbids']['other_comorbids'],

            'weight' => $visit->weight, // 17
            'height_' => $form['patient']['height'],
            'BMI_' => $form['patient']['weight'] && $form['patient']['height']
                ? ($form['patient']['weight'] / $form['patient']['height'] / $form['patient']['height'] * 10000)
                : null,

            // 18
            'Unvaccinated' => $form['vaccination']['unvaccinated'] ? 'YES' : 'NO',
            'vaccine dose 1' => $vaccinations->where('dose_no', 1)->first()?->brand,
            'date vaccination dose 1' => $vaccinations->where('dose_no', 1)->first()?->vaccinated_at->format('d M Y'),
            'vaccine dose 2' => $vaccinations->where('dose_no', 2)->first()?->brand,
            'date vaccination dose 2' => $vaccinations->where('dose_no', 2)->first()?->vaccinated_at->format('d M Y'),
            'vaccine dose 3' => $vaccinations->where('dose_no', 3)->first()?->brand,
            'date vaccination dose 3' => $vaccinations->where('dose_no', 3)->first()?->vaccinated_at->format('d M Y'),
            'vaccine dose 4' => $vaccinations->where('dose_no', 4)->first()?->brand,
            'date vaccination dose 4' => $vaccinations->where('dose_no', 4)->first()?->vaccinated_at->format('d M Y'),
            'vaccine dose 5' => $vaccinations->where('dose_no', 5)->first()?->brand,
            'date vaccination dose 5' => $vaccinations->where('dose_no', 5)->first()?->vaccinated_at->format('d M Y'),
            'Number of vaccination dose' => $form['vaccination']['doses'], // 19
            'date of latest vaccination' => $this->castDate($form['vaccination']['date_latest_vacciniated']), // 20
            'nNP swab result' => $form['management']['np_swab_result'], // 21
            'CT value' => $form['management']['np_swab_result_note'],
        ];
    }
}
