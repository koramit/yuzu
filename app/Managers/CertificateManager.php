<?php

namespace App\Managers;

use App\Models\User;
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

        if ($visit->atk_positive_case && !$visit->form['evaluation']['recommendation']) {
            $visit->forceFill([
                'form->evaluation->recommendation' => 'ATK positive',
                'form->evaluation->date_quarantine_end' => Carbon::create($visit->form['exposure']['date_atk_positive'])->addDays(10)->format('Y-m-d'),
            ])->save();
        }

        return [
            'slug' => $visit->slug,
            'patient_name' => 'HN '.$visit->hn.' '.$visit->patient->profile['first_name'],
            'result' => $visit->atk_positive_case ? 'ATK positive' : $visit->form['management']['np_swab_result'],
            'age' => $visit->age_at_visit,
            'risk' => $visit->atk_positive_case ? 'ATK positive' : $this->risk,
            'detail' => $this->risk === 'ไม่มีความเสี่ยง' ? '' : $this->getRiskDetail($visit),
            'last_exposure' => $this->lastExposure,
            'last_exposure_label' => $this->lastExposure ? Carbon::create($this->lastExposure)->format('M d') : null,
            'recommendation' => $visit->form['evaluation']['recommendation'] ?? null,
            'date_quarantine_end' => $visit->form['evaluation']['date_quarantine_end'] ?? null,
            'date_quarantine_end_label' => ($visit->form['evaluation']['date_quarantine_end'] ?? null) ? Carbon::create($visit->form['evaluation']['date_quarantine_end'])->format('M d') : null,
            'date_reswab' => $visit->form['evaluation']['date_reswab'] ?? null,
            'date_reswab_label' => ($visit->form['evaluation']['date_reswab'] ?? null) ? Carbon::create($visit->form['evaluation']['date_reswab'])->format('M d') : null,
            'note' => implode("\n", [$visit->vaccination_text, $visit->form['note']]),
            'checked' => false,
            'config' => $this->getConfig($visit),
            'medical_records' => $this->getMedicalRecords($visit),
            'screen_type' => $visit->screen_type,
            'atk_positive' => $visit->atk_positive_case,
            'date_atk_positive' => $visit->form['exposure']['date_atk_positive']
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

    protected function getConfig(Visit &$visit)
    {
        $dateRef = $visit->atk_positive_case ? $visit->form['exposure']['date_atk_positive'] : $visit->date_visit->format('Y-m-d');
        $result = $visit->atk_positive_case ? 'ATK positive' : $visit->form['management']['np_swab_result'];
        $dateQuarantineEnd = Carbon::create($this->lastExposure ?? $dateRef)->addDays(10); // CR 20220124 change 14 => 10 days
        $dateReswab = ($result === 'Inconclusive') ? Carbon::create($dateRef)->addDays(3) : $dateQuarantineEnd;
        // $dateReswab = Carbon::create($dateVisit)->addDays(3);  CR 20210923

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
                                'last_exposure_label' => ($record->form['exposure']['date_latest_expose'] ?? null) ? Carbon::create($record->form['exposure']['date_latest_expose'])->format('M d') : null,
                                'detail' => trim(implode(' ', [$record->form['exposure']['contact_type'], $record->form['exposure']['contact_detail'], $record->form['exposure']['hot_spot_detail'], $record->form['exposure']['other_detail']])),
                                'note' => $record->form['note'],
                            ];
                        });

        return $records;
    }

    public function update(Visit $visit, array $certificate, User $md)
    {
        $evaluation = $visit->form['evaluation'];
        if (
            ($evaluation['recommendation'] ?? null) === $certificate['recommendation']
            && ($evaluation['date_quarantine_end'] ?? null) === $certificate['date_quarantine_end']
            && ($evaluation['date_reswab'] ?? null) === $certificate['date_reswab']
        ) {
            return false;
        }

        $evaluation['recommendation'] = $certificate['recommendation'];
        $evaluation['date_quarantine_end'] = $certificate['date_quarantine_end'];
        $evaluation['date_reswab'] = $certificate['date_reswab'];
        $evaluation['md_name'] = $md->profile['full_name'];
        $evaluation['md_pln'] = $md->profile['pln'] ?? null;

        return $visit->forceFill(['form->evaluation' => $evaluation])->save();
    }
}
