<?php

namespace App\Actions;

use App\Traits\VisitStatQueryable;
use Exception;

class VisitLabResultStatAction
{
    use VisitStatQueryable;

    /**
     * @throws Exception
     */
    public function __invoke($start, $end, $full = false): array
    {
        $mode = $full ? 'full' : 'less';
        return cache()->remember("lab-result-$start-$end-$mode", now()->addDay(), function () use ($start, $end, $full) {
            $all = $this->query($start, $end, true, null, 1);
            $labels = array_keys($all->toArray());
            $all = array_values($all->toArray());

            $data = $this->query($start, $end, true, 1, 1, 'Detected');
            $publicPos = $this->fillZero($data, $labels);

            $data = $this->query($start, $end, true, 1, 1, 'Not detected');
            $publicNeg = $this->fillZero($data, $labels);

            $data = $this->query($start, $end, true, 2, 1, 'Detected');
            $staffPos = $this->fillZero($data, $labels);

            $data = $this->query($start, $end, true, 2, 1, 'Not detected');
            $staffNeg = $this->fillZero($data, $labels);

            if ($full) {
                $publicInc = $this->query($start, $end, false, 1, 1, 'Inconclusive');
                $staffInc = $this->query($start, $end, false, 2, 1, 'Inconclusive');
                $allInc = [];
            }

            $allPos = [];
            $allNeg = [];

            for($i = 0; $i < count($labels); $i++) {
                $allPos[] = $publicPos[$i] + $staffPos[$i];
                $allNeg[] = $publicNeg[$i] + $staffNeg[$i];
                if ($full) {
                    $allInc[] = $publicInc[$i] + $staffInc[$i];
                }
            }

            $datasets = [
                'all' => $all,
                'allPos' => $allPos,
                'allNeg' => $allNeg,
                'publicPos' => $publicPos,
                'publicNeg' => $publicNeg,
                'staffPos' => $staffPos,
                'staffNeg' => $staffNeg,
            ];

            if ($full) {
                $datasets['allInc'] = $allInc;
                $datasets['publicInc'] = $publicInc;
                $datasets['staffInc'] = $staffInc;
            }

            return [
                'labels' => $labels,
                'datasets' => $datasets,
            ];
        });
    }
}
