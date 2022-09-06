<?php

namespace App\Traits;

use App\Models\Visit;
use Exception;

trait VisitMinMaxDateAware
{
    /**
     * @throws Exception
     */
    protected function firstDay()
    {
        return cache()->rememberForever(
            'first-visit-date',
            fn () => Visit::query()->where('status', 4)->min('date_visit')
        );
    }

    /**
     * @throws Exception
     */
    protected function lastDay()
    {
        return cache()->remember(
            'last-visit-date',
            now()->addHours(12),
            fn () => Visit::query()->where('status', 4)->where('date_visit', '<', today())->max('date_visit'),
        );
    }
}
