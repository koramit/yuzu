<?php

namespace App\Http\Controllers;

use App\Events\LabReported;
use App\Events\VisitUpdated;
use App\Managers\ColabManager;
use App\Managers\NotificationManager;
use App\Models\Visit;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\Storage;

class KotoController extends Controller
{
    public function index()
    {
        Request::validate([
            'token' => [
                'required',
                function ($attribute, $value, $fail) {
                    if ($value !== config('app.ww_token')) {
                        $fail('The '.$attribute.' is invalid.');
                    }
                },
            ],
        ]);

        $todayStr = now()->format('Y-m-d');

        $remains = Visit::whereDateVisit($todayStr)
                    ->whereStatus(4)
                    ->whereSwabbed(true)
                    ->whereNull('form->management->np_swab_result')
                    ->count();

        return [
            'run' => $remains !== 0,
            'dateReff' => $todayStr
        ];
    }

    public function store()
    {
        Request::validate([
            'token' => [
                'required',
                function ($attribute, $value, $fail) {
                    if ($value !== config('app.ww_token')) {
                        $fail('The '.$attribute.' is invalid.');
                    }
                },
            ],
            'dateReff' => 'date',
            'excel' => 'file',
        ]);

        if (Request::has('error')) {
            (new NotificationManager)->notifySubscribers(mode: 'notify_croissant_need_help', text: 'มาดู croissant หน่อยยยย', sticker: 'warning');
            return;
        }

        $path = Request::file('excel')->store('temp');
        $filePath = storage_path('app/'.$path);
        $todayStr = Request::input('dateReff');
        $updatedCount = (new ColabManager)->manage(filePath: $filePath, dateVisit: $todayStr);

        $remains = Visit::whereDateVisit($todayStr)
                        ->whereStatus(4)
                        ->whereSwabbed(true)
                        ->whereNull('form->management->np_swab_result')
                        ->count();

        if ($updatedCount) {
            $visit = Visit::orderDesc('updated_at')->first();
            VisitUpdated::dispatch($visit);
            LabReported::dispatch($visit);
        }

        Storage::delete($path);

        return [
            'finished' => $remains === 0
        ];
    }
}
