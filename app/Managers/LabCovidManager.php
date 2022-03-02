<?php

namespace App\Managers;

use App\APIs\SiITLabAPI;
use App\Models\Visit;
use Illuminate\Database\Eloquent\Collection;

class LabCovidManager
{
    protected $api;
    protected $labIds = [
        'pcr' => '204592',
        'rapid_pcr' => '204593'
    ];

    protected $labNames = [
        'pcr' => '**SARS-CoV-2(COVID-19)RNA',
        'rapid_pcr' => ''
    ];

    public function __construct()
    {
        $this->api = new SiITLabAPI;
    }

    public function fetchPCR(Collection $visits)
    {
        $matchCount = 0;
        foreach ($visits as $visit) {
            $matchCount += ($this->manage($visit, 'pcr') === 1 ? 1 : 0);
        }
        // $visits->each(function ($visit) use ($matchCount) {
        //     $matchCount += $this->manage($visit, 'pcr') === 1 ? 1 : 0;
        // });

        echo $visits[0]->date_visit->format('Y-d-m') . ' => ' . $visits->count() . ' : ' . $matchCount . "\n";
    }

    protected function manage(Visit &$visit, string $lab)
    {
        $dateLab = $visit->date_visit->format('Y-m-d');
        $results = $this->api->getLabs(hn: $visit->hn, dateLab: $dateLab, labs: [$this->labIds[$lab]]);

        if ($results === false) {
            return; // should notify if to many errors
        }

        if (!count($results)) {
            return; // no results;
        }

        $result = false;
        foreach ($results as $record) {
            if (
                $record['ORDER_DATE'] === $dateLab
                || $record['SPECIMEN_RECEIVED'] === $dateLab
                || $record['REPORT_DATE'] === $dateLab
            ) {
                $result = $record;
                break;
            }
        }

        if (!$result) {
            return; // no result on the date
        }

        $labResultFinally = false;
        foreach ($result['RESULT'] as $record) {
            if ($record['TI_CODE'] === $this->labIds[$lab]) {
                $labResultFinally = $record;
                break;
            }
        }

        if (!$labResultFinally) {
            return; // no lab in result 😓
        }

        if (!$labResultFinally['RESULT_CHAR']) {
            return; // pending
        }

        // return $visit->form['management']['np_swab_result'] === $labResultFinally['RESULT_CHAR'] ? 1 : null;

        if ($visit->form['management']['np_swab_result'] != $labResultFinally['RESULT_CHAR']) {
            echo $visit->id . ' => ' . $visit->form['management']['np_swab_result'] . ' : ' . $labResultFinally['RESULT_CHAR'] . "\n";
            return;
        }

        return 1;
    }
}
