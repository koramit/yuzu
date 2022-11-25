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
        $now = now()->tz(7);
        $start = now()->create('2022-11-25 00:00 +7');
        $end = now()->create('2022-12-01 00:00 +7');

        if ($request->route()->getName() !== 'poster') {
            return redirect()->route('poster');
        }

        if (! $now->between($start, $end)) {
            return Inertia::render('PosterVoid');
        }

        if (! $request->session()->get('poster-visitor', null)) {
            $request->session()->put('poster-visitor', true);
            cache()->increment('poster-2022-visitor-count');
        }

        cache()->increment('poster-2022-view-count');

        return Inertia::render('PosterShow', [
            'firstDay' => $this->firstDay(),
            'lastDay' => $this->lastDay(),
        ]);
    }
}
