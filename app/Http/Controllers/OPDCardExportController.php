<?php

namespace App\Http\Controllers;

use App\Models\LoadDataRecord;
use App\Models\Visit;
use App\Traits\OPDCardExportable;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request;
use Rap2hpoutre\FastExcel\Facades\FastExcel;

class OPDCardExportController extends Controller
{
    use OPDCardExportable;

    public function __invoke()
    {
        $dateStr = Request::input('date_visit', now('asia/bangkok')->format('Y-m-d'));

        $visits = Visit::with('patient')
                       ->whereDateVisit($dateStr)
                       ->whereIn('status', [3, 4])
                       ->orderBy('discharged_at')
                       ->get()
                       ->transform(function ($visit) {
                           return $this->allData($visit);
                       });

        $filename = 'ARI Clinic OPD cards@'.$dateStr.'.xlsx';

        LoadDataRecord::create([
            'export' => true,
            'configs' => [
                'date_visit' => $dateStr,
                'data' => 'opd_card_all_type',
            ],
            'user_id' => Auth::id(),
        ]);

        return FastExcel::data($visits)->download($filename);
    }

    protected function keyname()
    {
        $binding = [

        ];
    }
}
