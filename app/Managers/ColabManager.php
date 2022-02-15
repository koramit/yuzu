<?php

namespace App\Managers;

use App\Models\Colab;
use App\Models\Visit;
use Carbon\Carbon;
use Exception;
use Rap2hpoutre\FastExcel\Facades\FastExcel;

class ColabManager
{
    public function manage(string $filePath, string $dateVisit)
    {
        $colabs = FastExcel::import($filePath)
                        ->filter(fn ($l) => $l['CLINIC_CODE'] == '1402')
                        ->transform(fn ($l) => [
                            'hn' => $l['HN'],
                            'id' => $l['LAB_NO'],
                            'received_at' => $this->castDatetime($l['RECIEVE_DATE']),
                            'approved_at' => $this->castDatetime($l['APPROVE_DATE']),
                            'specimen' => $l['SPECIMEN'],
                            'result' => $l['RESULT'],
                            'remark' => str_replace("\n", ' | ', $l['REMARK']),
                            'reporter' => $l['REPORTER'],
                            'approver' => $l['APPROVER'],
                        ])->values();

        $visits = Visit::with('patient')
                        ->whereDateVisit($dateVisit)
                        ->whereSwabbed(true)
                        ->whereStatus(4)
                        ->whereNull('form->management->np_swab_result')
                        ->get();

        if (!$visits->count()) {
            return 0;
        }

        $count = 0;

        foreach ($colabs as $lab) {
            $index = $visits->search(function ($v) use ($lab) {
                return $v->hn === $lab['hn'];
            });
            if ($index === false) {
                continue;
            }

            $visit = $visits[$index];

            $colab = Colab::find($lab['id']);
            if ($colab) {
                // update if needed
                continue;
            }

            $lab['visit_id'] = $visit->id;
            $colab = Colab::create($lab);

            $visit->forceFill([
                'form->management->np_swab_result' => $colab->result,
                'form->management->np_swab_result_note' => $colab->remark,
            ])->save();

            $count++;
        }

        return $count;
    }

    protected function castDatetime(string $value)
    {
        try {
            return Carbon::create($value)->addHours(-7)->format('Y-m-d H:i:s');
        } catch (Exception $e) {
            return null;
        }
    }
}
