<?php

namespace App\Managers;

use App\APIs\SiITLabAPI;
use App\Models\Visit;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;

class LabCovidManager
{
    protected $api;
    protected $labs;

    public function __construct()
    {
        $this->api = new SiITLabAPI;

        $this->labs = [
            'pcr' => [
                'service_id' => ['204592', '5565'],
                'ti_code' => collect(['204592', '556A03']),
                'result' => collect(['detected', 'not detected', 'inconclusive']),
            ],
        ];
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
            if ($this->manage($visit, 'pcr') === 'ok') {
                $matchCount++;
            }
        }

        echo $visits[0]->date_visit->format('Y-m-d') . ' => ' . $visits->count() . ' : ' . $matchCount . "\n";
    }

    protected function manage(Visit &$visit, string $lab)
    {
        // call lab
        $dateLab = $visit->date_visit->format('Y-m-d');
        $results = $this->api->getLabs(hn: $visit->hn, dateLab: $dateLab, labs: $this->labs[$lab]['service_id']);

        // validate resutls
        if ($results === false) {
            echo $visit->hn .' : ' . $dateLab ." => call error\n";
            return 'error'; // should notify if to many errors
        }
        if (!count($results)) {
            echo $visit->hn .' : ' . $dateLab ." => no result\n";
            return 'no lab'; // no results;
        }
        $filtered = collect($results)->filter(
            fn ($r) => $r['ORDER_DATE'] === $dateLab || $r['SPECIMEN_RECEIVED'] === $dateLab || $r['REPORT_DATE'] === $dateLab
        );

        $recordIndex = $filtered->search(function ($r) use ($lab) {
            $foundIndex = collect($r['RESULT'])->search(
                fn ($l) => $this->labs[$lab]['ti_code']->contains($l['TI_CODE'])
                        && $this->labs[$lab]['result']->contains(strtolower($l['RESULT_CHAR'] ?? ''))
            );

            if ($foundIndex === false) {
                return false;
            }

            $r['selected'] = $r['RESULT'][$foundIndex];
            return true;
        });

        if ($recordIndex === false) {
            echo $visit->hn .' : ' . $dateLab ." => pending\n";
            return 'pending'; // pending
        }

        // update
        $record = $filtered[$recordIndex];
        if ($record['selected']['RESULT_CHAR'] != $visit->form['management']['np_swab_result']) {
            echo $visit->hn .' : ' . $dateLab ." => not match\n";
            return 'not match';
        }

        return 'ok';
    }
}
