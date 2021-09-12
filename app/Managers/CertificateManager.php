<?php

namespace App\Managers;

use App\Models\Visit;
use Illuminate\Support\Carbon;

class CertificateManager
{
    protected $risk;
    protected $lastExposure;

    public function getData(Visit $visit)
    {
        $this->risk = $visit->form['exposure']['evaluation'];
        $this->lastExposure = $visit->form['exposure']['date_latest_expose'];

        return [
            'slug' => $visit->slug,
            'patient_name' => 'HN '.$visit->hn.' '.$visit->patient->profile['first_name'],
            'result' => $visit->form['management']['np_swab_result'],
            'age' => $visit->age_at_visit,
            'risk' => $this->risk,
            'detail' => $this->risk === 'ไม่มีความเสี่ยง' ? '' : $this->getRiskDetail($visit),
            'last_exposure' => $this->lastExposure,
            'last_exposure_label' => $this->lastExposure ? Carbon::create($this->lastExposure)->format('M d') : null,
            'recommendation' => $visit->form['evaluation']['recommendation'],
            'date_quarantine_end' => $visit->form['evaluation']['date_quarantine_end'] ?? null,
            'date_reswab' => $visit->form['evaluation']['date_reswab'] ?? null,
            'note' => $visit->form['note'],
            'checked' => false,
            'config' => $this->getConfig($visit->date_visit->format('Y-m-d')),
            'medical_records' => $this->getMedicalRecords($visit),
            'screen_type' => $visit->screen_type,
        ];
    }

    protected function getRiskDetail(Visit $visit)
    {
        $exposure = $visit->form['exposure'];

        if ($this->risk !== 'ความเสี่ยงเดิม') {
            return trim(implode(' ', [$exposure['contact_type'], $exposure['contact_detail'], $exposure['hot_spot_detail'], $exposure['other_detail']]));
        }

        $firstVisit = Visit::wherePatientId($visit->patient_id)
                           ->where('date_visit', '<', $visit->date_visit->format('Y-m-d'))
                           ->whereScreenType(1)
                           ->whereStatus(4)
                           ->orderByDesc('date_visit')
                           ->first();

        if (! $firstVisit) {
            return '';
        }

        $exposure = $firstVisit->form['exposure'];
        $this->lastExposure = $firstVisit->form['exposure']['date_latest_expose'];

        return trim(implode(' ', [$exposure['contact_type'], $exposure['contact_detail'], $exposure['hot_spot_detail'], $exposure['other_detail']]));
    }

    protected function getConfig($dateVisit)
    {
        $dateQuarantineEnd = Carbon::create($this->lastExposure ?? $dateVisit)->addDays(14);
        $dateReswab = Carbon::create($dateVisit)->addDays(3);

        return [
            'date_quarantine_end' => $dateQuarantineEnd->format('Y-m-d'),
            'date_quarantine_end_label' => $dateQuarantineEnd->format('M d'),
            'date_reswab' => $dateReswab->format('Y-m-d'),
            'date_reswab_label' => $dateReswab->format('M d'),
        ];
    }

    protected function getMedicalRecords(Visit $visit)
    {
        if ($visit->screen_type === 'เริ่มตรวจใหม่') {
            return [];
        }

        $records = Visit::wherePatientId($visit->patient_id)
                        ->where('date_visit', '>=', Carbon::create($visit->date_visit->format('Y-m-d'))->addDays(-14)->format('Y-m-d'))
                        ->where('date_visit', '<', $visit->date_visit->format('Y-m-d'))
                        ->whereStatus(4)
                        ->orderByDesc('date_visit')
                        ->get()
                        ->transform(function ($record) {
                            return [
                                'date_visit' => $record->date_visit->format('M d'),
                                'result' => $record->form['management']['np_swab_result'] ?? null,
                                'scrren_type' => $record->screen_type,
                                'risk' => $record->form['exposure']['evaluation'],
                                'last_exposure' => $record->form['exposure']['date_latest_expose'],
                                'detail' => trim(implode(' ', [$record->form['exposure']['contact_type'], $record->form['exposure']['contact_detail'], $record->form['exposure']['hot_spot_detail'], $record->form['exposure']['other_detail']])),
                                'note' => $record->form['note'],
                            ];
                        });

        return $records;
    }
}
