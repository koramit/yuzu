<?php

namespace App\Http\Controllers;

use App\Models\Visit;
use Illuminate\Http\Request;
use Inertia\Inertia;

class PosterController extends Controller
{
    /**
     * @throws \Exception
     */
    public function __invoke(Request $request)
    {
        $firstDay = cache()->rememberForever(
            'first-visit-date',
            fn () => Visit::query()->where('status', 4)->min('date_visit')
        );
        $lastDay = cache()->remember(
            'last-visit-date',
            now()->addDay(),
            fn () => Visit::query()->where('status', 4)->max('date_visit'),
        );
        if ($request->route()->getName() === 'poster') {
            if (
                now()->tz(7)->lessThan(now()->create('2022-09-08 00:00 +7'))
                || now()->tz(7)->greaterThan(now()->create('2022-09-10 00:00 +7'))
            ) {
                return Inertia::render('PosterVoid');
            }
        }


        return Inertia::render('PosterShow', [
            'firstDay' => $firstDay,
            'lastDay' => $lastDay,
        ]);
    }
}
