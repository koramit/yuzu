<?php

namespace App\Managers;

use App\APIs\SiITLabAPI;
use App\Models\Visit;
use Carbon\Carbon;
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

    public function run(string $dateStart, int $days)
    {
        for ($i = 0; $i <= $days; $i++) {
            $dateVisit = Carbon::create($dateStart)->addDays($i)->format('Y-m-d');

            $visits = Visit::whereDateVisit($dateVisit)
                            ->whereSwabbed(true)
                            ->get();

            $this->fetchPCR($visits);
        }
    }

    public function fetchPCR(Collection $visits)
    {
        $matchCount = 0;
        foreach ($visits as $visit) {
            $matchCount += ($this->manage($visit, 'pcr') === 1 ? 1 : 0);
        }

        echo $visits[0]->date_visit->format('Y-d-m') . ' => ' . $visits->count() . ' : ' . $matchCount . "\n";
    }

    protected function manage(Visit &$visit, string $lab)
    {
        $dateLab = $visit->date_visit->format('Y-m-d');
        $results = $this->api->getLabs(hn: $visit->hn, dateLab: $dateLab, labs: [$this->labIds[$lab]]);

        if ($results === false) {
            echo $visit->hn .'-' . $dateLab ." => call error\n";
            return; // should notify if to many errors
        }

        if (!count($results)) {
            echo $visit->hn .'-' . $dateLab ." => no result\n";
            return; // no results;
        }

        foreach ($results as $record) {
            if (
                $record['ORDER_DATE'] === $dateLab
                || $record['SPECIMEN_RECEIVED'] === $dateLab
                || $record['REPORT_DATE'] === $dateLab
            ) {
                $labResult = false;
                foreach ($record['RESULT'] as $result) {
                    if (
                        $result['TI_CODE'] === $this->labIds[$lab] // exclude specimen
                        && collect(['detected', 'not detected', 'inconclusive'])->contains(strtolower($result['RESULT_CHAR'] ?? ''))
                    ) {
                        $labResult = $result;
                        break;
                    }
                }

                if ($labResult) {
                    if ($visit->form['management']['np_swab_result'] != $labResult['RESULT_CHAR']) {
                        echo $visit->id . ' => ' . $visit->form['management']['np_swab_result'] . ' : ' . $labResult['RESULT_CHAR'] . "\n";
                        return;
                    } else {
                        return 1;
                    }
                }
            }
        }

        echo $visit->hn .'-' . $dateLab ." => pending\n";
        return; // pending
    }
}
