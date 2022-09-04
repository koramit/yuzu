<?php

namespace App\Http\Controllers;

use App\Actions\VisitLabPositiveStatAction;
use App\Actions\VisitLabResultStatAction;
use App\Actions\VisitPCRStatAction;
use App\Actions\VisitServiceStatAction;
use App\Traits\VisitMinMaxDateAware;
use Exception;
use Illuminate\Http\Request;

class StatDataController extends Controller
{
    use VisitMinMaxDateAware;

    /**
     * @throws Exception
     */
    public function __invoke(Request $request)
    {
        $validated = $request->validate([
            'start' => 'nullable|date',
            'end' => 'nullable|date',
        ]);

        $minDate = $this->firstDay();
        $maxDate = $this->lastDay();

        $start = $validated['start'] ?? $minDate;
        $end = $validated['end'] ?? $maxDate;

        if ($start < $minDate) {
            $start = $minDate;
        }
        if ($end > $maxDate) {
            $end = $maxDate;
        }

        $name = explode('.', $request->route()->getName())[1];
        if ($name === 'service') {
            return (new VisitServiceStatAction())($start, $end);
        } elseif ($name === 'lab-positive') {
            return (new VisitLabPositiveStatAction())($start, $end);
        } elseif ($name === 'lab-result') {
            return (new VisitLabResultStatAction())($start, $end);
        }

        return [];
    }
}
