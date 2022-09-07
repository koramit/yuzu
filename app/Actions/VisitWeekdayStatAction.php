<?php

namespace App\Actions;

use Exception;
use Illuminate\Support\Facades\DB;

class VisitWeekdayStatAction
{
    /**
     * @throws Exception
     */
    public function __invoke($start, $end): array
    {
        return cache()->remember("service-weekday-$start-$end", now()->addDay(), function () use ($start, $end) {
            cache()->increment('service-weekday-query-count');
            $labels = ['จันทร์', 'อังคาร', 'พุธ', 'พฤหัสบดี', 'ศุกร์', 'เสาร์', 'อาทิตย์'];
            $dividers = [0,0,0,0,0,0,0];
            $startDate = now()->create($start);
            $endDate = now()->create($end);
            while ($startDate->lessThanOrEqualTo($endDate)) {
                $weekday = $startDate->weekDay();
                if ($weekday === 0) {
                    $weekday = 6;
                } else {
                    $weekday = $weekday - 1;
                }
                $dividers[$weekday] = $dividers[$weekday] + 1;
                $startDate->addDay();
            }

            $data = DB::table('visits')
                ->selectRaw('COUNT(*) as cases')
                ->selectRaw('WEEKDAY(enlisted_screen_at) as at_weekday')
                //->selectRaw('HOUR(CONVERT_TZ(enlisted_screen_at, "+00:00", "+07:00")) as at_hour')
                ->where('status', 4)
                ->where('date_visit', '>=', $start)
                ->where('date_visit', '<=', $end)
                //->groupBy('at_hour')
                ->groupBy('at_weekday')
                ->get();

            $all = [];
            foreach ($labels as $index => $day) {
                $all[] = ($data->where('at_weekday', $index)->first()?->cases ?? 0) / $dividers[$index];
            }

            return [
                'labels' => $labels,
                'datasets' => [
                    'all' => $all,
                ],
                'categories' => collect($labels)->map(fn ($d, $i) => ['label' => $d, 'index' => $i, 'show' => true]),
            ];
        });
    }
}
