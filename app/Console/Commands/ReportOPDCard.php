<?php

namespace App\Console\Commands;

use App\Models\Visit;
use App\Traits\OPDCardExportable;
use Illuminate\Console\Command;
use Rap2hpoutre\FastExcel\FastExcel;

class ReportOPDCard extends Command
{
    use OPDCardExportable;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'report-excel:opd-card {begin} {end}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'opd card to excel';


    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $begin = $this->argument('begin');
        $end = $this->argument('end');
        $filename = storage_path("app/temp/super-valentine-{$begin}-to-{$end}.xlsx");

        $visits = Visit::with('patient')
                       ->with(['vaccinations' => fn ($q) => $q->select(['vaccinated_at', 'brand_id', 'dose_no', 'patient_id'])->orderBy('dose_no')])
                       ->where('swabbed', true)
                       ->where('patient_type', 2)
                       ->whereBetween('date_visit', [$begin, $end])
                       ->where('status', 4)
                       ->orderBy('discharged_at')
                       ->get()
                       ->transform(function ($visit) {
                           return $this->allData($visit);
                       });

        (new FastExcel($visits))->export($filename);

        return 0;
    }
}
