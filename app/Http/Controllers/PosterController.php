<?php

namespace App\Http\Controllers;

use App\Traits\VisitMinMaxDateAware;
use Exception;
use Illuminate\Http\Request;
use Inertia\Inertia;

class PosterController extends Controller
{
    use VisitMinMaxDateAware;

    /**
     * @throws Exception
     */
    public function __invoke(Request $request)
    {
        if ($request->route()->getName() === 'poster') {
            if (
                now()->tz(7)->lessThan(now()->create('2022-09-08 00:00 +7'))
                || now()->tz(7)->greaterThan(now()->create('2022-09-10 00:00 +7'))
            ) {
                return Inertia::render('PosterVoid');
            }
        }

        return Inertia::render('PosterShow', [
            'firstDay' => $this->firstDay(),
            'lastDay' => $this->lastDay(),
        ]);
    }
}
