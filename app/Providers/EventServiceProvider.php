<?php

namespace App\Providers;

use App\Events\LabReported;
use App\Events\LabAlerted;
use App\Events\Registered;
use App\Events\VisitUpdated;
use App\Listeners\InitRole;
use App\Listeners\ManageVisitEvent;
use App\Listeners\NotifyLabSubscribers;
use App\Listeners\NotifyLabAlerted;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event listener mappings for the application.
     *
     * @var array
     */
    protected $listen = [
        Registered::class => [
            InitRole::class,
        ],
        VisitUpdated::class => [
            ManageVisitEvent::class,
        ],
        LabReported::class => [
            NotifyLabSubscribers::class
        ],
        LabAlerted::class => [
            NotifyLabAlerted::class
        ],
        LabNoResult::class => [
            NotifyLabNoResult::class
        ]
    ];

    /**
     * Register any events for your application.
     *
     * @return void
     */
    public function boot()
    {
        //
    }
}
