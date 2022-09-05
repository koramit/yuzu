<?php

namespace App\Traits;

use App\Models\Visit;
use Illuminate\Support\Collection;

trait VisitStatQueryable
{
    protected function query($start, $end, $label = null, $patientType = null, $swab = null, $result = null, $asymptom = null, $vaccinated = null): Collection
    {
        $base = Visit::query()
            ->selectRaw('COUNT(id) as cases')
            ->when($label, fn ($query) => $query->selectRaw('DATE_FORMAT(date_visit, "%e %b %y") as visited_at'))
            ->where('status', 4)
            ->where('date_visit', '>=', $start)
            ->where('date_visit', '<=', $end)
            ->when($patientType, fn ($query) => $query->where('patient_type', $patientType))
            ->when($swab !== null, fn ($query) => $query->where('swabbed', $swab))
            ->when($result, fn ($query) => $query->where('lab_result_stored', $result))
            ->when($asymptom !== null, fn ($query) => $query->where('asymptomatic_stored', $asymptom))
            ->when($vaccinated !== null, function ($query) use ($vaccinated) {
                $query->when($vaccinated === true, function ($query) {
                    $query->whereHas('vaccinations', fn ($q) => $q->whereRaw('vaccinated_at < visits.date_visit'));
                })->when($vaccinated === false, function ($query) {
                    $query->whereDoesntHave('vaccinations', fn ($q) => $q->whereRaw('vaccinated_at < visits.date_visit'));
                });
            })
            ->orderBy('date_visit')
            ->groupBy('date_visit');

        return $label
            ? $base->pluck('cases', 'visited_at')
            : $base->pluck('cases');
    }

    protected function fillZero(&$data, &$keys): array
    {
        $filled = [];
        foreach ($keys as $key) {
            $filled[] = $data[$key] ?? 0;
        }

        return $filled;
    }
}
