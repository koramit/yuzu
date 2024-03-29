<?php

namespace App\Managers;

use App\APIs\SiITLabAPI;
use App\Events\LabAlerted;
use App\Events\LabNoResult;
use App\Events\LabReported;
use App\Events\VisitUpdated;
use App\Models\Visit;
use Illuminate\Support\Facades\Log;

class LabCovidManager
{
    protected $api;
    protected $labs;
    protected $labSelected;
    protected $reported;
    protected $detected;
    protected $noResult;

    public function __construct()
    {
        $this->api = new SiITLabAPI;

        $this->labs = [
            'pcr' => [
                'service_id' => ['204592', '204593', '5565'],
                'ti_code' => collect(['204592', '204593', '556A03']),
                'result' => collect(['detected', 'not detected', 'inconclusive', 'invalid']),
            ],
        ];

        $this->reported = null;
        $this->detected = null;
        $this->noResult = [];
    }

    public function fetchPCR(string $dateVisit)
    {
        $visits = Visit::whereDateVisit($dateVisit)
                        ->whereNull('form->management->np_swab_result')
                        ->whereSwabbed(true)
                        ->get();

        if (! $visits->count()) {
            return false;
        }

        $count = [
            'start' => $visits->count(),
            'error' => 0,
            'no lab' => 0,
            'pending' => 0,
            'reported' => 0,
            'remains' => $visits->count(),
        ];

        foreach ($visits as $visit) {
            $result = $this->manage($visit, 'pcr');
            if ($result === 'error') {
                $count['error']++;
            } elseif ($result === 'no lab') {
                $count['no lab']++;
                $this->noResult[] = $visit;
            } elseif ($result === 'pending') {
                $count['pending']++;
            } elseif ($result === 'reported') {
                $count['reported']++;
            }
        }

        if ($count['error']) {
            Log::notice('lab error '. $count['error'].' of '.$visits->count());
        }

        if (count($this->noResult)) {
            $notice = "no lab\n";
            foreach($this->noResult as $visit) {
                $notice .= "{$visit->hn}\n";
            }
            Log::notice($notice);
        }

        if (now()->hour >= 13 && count($this->noResult)) { // after 20:00 only
            LabNoResult::dispatch($this->noResult);
        }

        if ($count['reported']) {
            VisitUpdated::dispatch($this->reported ?? $this->detected); // in case of last lab is detected
            LabReported::dispatch($this->detected ?? $this->reported);
            $count['remains'] = $count['start'] - $count['reported'];
        }

        return $count;
    }

    protected function manage(Visit &$visit, string $lab)
    {
        // call lab
        $dateLab = $visit->date_visit->format('Y-m-d');
        $results = $this->api->getLabs(hn: $visit->hn, dateLab: $dateLab, labs: $this->labs[$lab]['service_id']);

        // validate resutls
        if ($results === false) {
            return 'error';
        }
        if (empty($results)) {
            return 'no lab';
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

            $this->labSelected = $r['RESULT'][$foundIndex];
            return true;
        });

        if ($recordIndex === false) {
            return 'pending'; // pending
        }

        // update
        $record = $filtered[$recordIndex];
        $specimen = collect($record['RESULT'])->filter(fn ($r) => ($r['TI_NAME'] ?? '') === 'SPECIMEN')[0] ?? null;
        $transaction = [
            'lab_no' => $record['LAB_NO'],
            'ordered_at' => $record['ORDER_DATE'] . ' ' . $record['ORDER_TIME'],
            'received_at' => $record['SPECIMEN_RECEIVED'] . ' ' . $record['SPECIMEN_RECEIVED_TIME'],
            'reported_at' => $record['REPORT_DATE'] . ' ' . $record['REPORT_TIME'],
            'specimen' => $specimen ? ($specimen['RESULT_CHAR'] ?? null) : null,
            'lab_code' => $record['SERV_ID'] ?? null,
            'lab_name' => $record['SERV_DESC'] ?? null,
        ];

        $visit->forceFill([
            'form->management->np_swab_result' => $this->labSelected['RESULT_CHAR'],
            'form->management->np_swab_result_note' => ($record['NOTE'] ?? null) ? str_replace("\r\n", ' | ', $record['NOTE']) : null,
            'form->management->np_swab_result_transaction' => $transaction,
        ])->save();

        if (strtolower($this->labSelected['RESULT_CHAR']) === 'invalid') {
            LabAlerted::dispatch($visit, 'invalid');
        }

        if (!$this->detected && strtolower($this->labSelected['RESULT_CHAR']) === 'detected') {
            $this->detected = $visit;
        } elseif (!$this->reported) {
            $this->reported = $visit;
        }

        return 'reported';
    }
}
