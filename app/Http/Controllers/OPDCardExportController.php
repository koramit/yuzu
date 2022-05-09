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
                       ->with(['vaccinations' => fn ($q) => $q->select(['vaccinated_at', 'brand_id', 'dose_no', 'patient_id'])->where('vaccinated_at', '<', $dateStr)->orderBy('dose_no')])
                       ->whereDateVisit($dateStr)
                       ->whereIn('status', [3, 4])
                       ->orderBy('discharged_at')
                       ->get()
                       ->transform(function ($visit) {
                           return $this->allData($visit) + [
                            'vaccine_dose_1' => $visit->vaccinations()->where('dose_no', 1)->first()?->brand,
                            'vaccine_dose_2' => $visit->vaccinations()->where('dose_no', 2)->first()?->brand,
                            'vaccine_dose_3' => $visit->vaccinations()->where('dose_no', 3)->first()?->brand,
                            'vaccine_dose_4' => $visit->vaccinations()->where('dose_no', 4)->first()?->brand,
                            'vaccine_dose_5' => $visit->vaccinations()->where('dose_no', 5)->first()?->brand,
                           ];
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
