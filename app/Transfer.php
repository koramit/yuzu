<?php
namespace App;

use App\Managers\PatientManager;
use App\Models\Visit;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class Transfer
{
    public function push()
    {
        Visit::whereDateVisit('2021-10-19')
             ->whereStatus(4)
             ->each(function ($visit) {
                 $response = Http::post(env('TRANSFER_ENDPOINT'), $visit->toArray());
                 echo $visit->id .' '.$response->json()['message']."\n";
             });
    }

    public function set($visit)
    {
        $patient = (new PatientManager)->manage($visit['hn']);

        if (! $patient['found']) {
            return [
                'message' => 'no patient',
            ];
        }

        $old = Visit::wherePatientId($patient['patient']->id)
                    ->whereStatus(4)
                    ->whereDateVisit($visit['date_visit'])
                    ->first();

        if ($old) {
            return [
                'message' => 'duplicate case',
            ];
        }

        $new = new Visit();
        $new->slug = Str::uuid()->toString();
        $new->date_visit = $visit['date_visit'];
        $new->patient_type = $visit['patient_type'];
        $new->screen_type = $visit['screen_type'];
        $new->status = $visit['status'];
        $new->form = $visit['form'];
        $new->patient_id = $patient['patient']->id;
        $new->swabbed = $visit['swabbed'];
        $new->enlisted_screen_at = $visit['enlisted_screen_at'];
        $new->enqueued_at = $visit['enqueued_at'];
        $new->enlisted_exam_at = $visit['enlisted_exam_at'];
        $new->enlisted_swab_at = $visit['enlisted_swab_at'];
        $new->enqueued_swab_at = $visit['enqueued_swab_at'];
        $new->discharged_at = $visit['discharged_at'];
        $new->authorized_at = $visit['authorized_at'];
        $new->attached_opd_card_at = $visit['attached_opd_card_at'];
        $new->save();

        return [
            'message' => 'ok',
        ];
    }
}
