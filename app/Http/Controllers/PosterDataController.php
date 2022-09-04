<?php

namespace App\Http\Controllers;

use App\Models\Visit;
use App\Models\VisitStat;
use Exception;
use Illuminate\Http\Request;

class PosterDataController extends Controller
{
    /**
     * @throws Exception
     */
    public function __invoke(Request $request)
    {
        $validated = $request->validate([
            'start' => 'nullable|date',
            'end' => 'nullable|date',
        ]);

        $minDate = cache('first-visit-date');
        $maxDate = cache('last-visit-date');

        $start = $validated['start'] ?? $minDate;
        $end = $validated['end'] ?? $maxDate;

        if ($start < $minDate) {
            $start = $minDate;
        }
        if ($end > $maxDate) {
            $end = $maxDate;
        }

        return cache()->remember("service-chart-$start-$end", now()->addDay(), function () use($start, $end) {
            $all = $this->query($start, $end, true);
            $staff = $this->query($start, $end, false, 2)->toArray();
            $public = $this->query($start, $end, false, 1)->toArray();
            $publicSwab = $this->query($start, $end, false, 1, 1)->toArray();
            $labels = array_keys($all->toArray());
            $all = array_values($all->toArray());

            return [
                'labels' => $labels,
                'datasets' => [
                    'all' => $all,
                    'staff' => $staff,
                    'public' => $public,
                    'publicSwab' => $publicSwab,
                ],
            ];
        });
    }

    protected function query($start, $end, $label = null, $patientType = null, $swab = null)
    {
        $base = Visit::query()
            ->selectRaw('COUNT(*) as cases')
            ->when($label, fn ($query) => $query->selectRaw('DATE_FORMAT(date_visit, "%e %b %y") as visited_at'))
            ->where('status', 4)
            ->where('date_visit', '>=', $start)
            ->where('date_visit', '<=', $end)
            ->when($patientType, fn ($query) => $query->where('patient_type', $patientType))
            ->when($swab !== null, fn ($query) => $query->where('swabbed', $swab))
            ->orderBy('date_visit')
            ->groupBy('date_visit');

        return $label
            ? $base->pluck('cases', 'visited_at')
            : $base->pluck('cases');
    }

    protected function useModel($start, $end)
    {
        // low query more memory
        $stat = VisitStat::query()
            ->selectRaw('COUNT(*) as cases, date_visit, patient_type, swabbed')
            ->where('status', 4)
            ->where('date_visit', '>=', $start)
            ->where('date_visit', '<=', $end)
            ->orderBy('date_visit')
            ->groupBy('date_visit')
            ->groupBy('patient_type')
            ->groupBy('swabbed')
            ->get();

        $data = [];
        foreach ($stat as $item) {
            $day = $item['date_visit']->format('d M y');
            if (! isset($data[$day])) {
                $data[$day]['all'] = 0;
                $data[$day]['public'] = 0;
                $data[$day]['public_swab'] = 0;
                $data[$day]['staff'] = 0;
                $data[$day]['staff_swab'] = 0;
            }
            if ($item['patient_type'] === 1 ) {
                if ($item['swabbed']) {
                    $data[$day]['public_swab'] = $data[$day]['public_swab'] + $item['cases'];
                } else {
                    $data[$day]['public'] = $data[$day]['public'] + $item['cases'];
                }
            } else {
                if ($item['swabbed']) {
                    $data[$day]['staff_swab'] = $data[$day]['staff_swab'] + $item['cases'];
                } else {
                    $data[$day]['staff'] = $data[$day]['staff'] + $item['cases'];
                }
            }
            $data[$day]['all'] = $data[$day]['all'] + $item['cases'];
        }
        $labels = array_keys($data);
        $all = [];
        $staff = [];
        $public = [];
        $publicSwab = [];
        foreach ($data as $day => $group) {
            $all[] = $group['all'];
            $staff[] = $group['staff'] + $group['staff_swab'];
            $public[] = $group['public'] + $group['public_swab'];
            $publicSwab[] = $group['public_swab'];
        }
        return [
            'labels' => $labels,
            'datasets' => [
                'all' => $all,
                'staff' => $staff,
                'public' => $public,
                'publicSwab' => $publicSwab,
            ],
        ];
    }
}
