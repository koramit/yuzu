<?php

namespace App\Console\Commands;

use App\Managers\OPDCardExportManager;
use App\Models\Visit;
use Illuminate\Console\Command;
use Rap2hpoutre\FastExcel\FastExcel;
use App\Traits\OPDCardExportable;

class ExportOPDCardPinyo extends Command
{
    use OPDCardExportable;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'report-excel:opd-card-pinyo {mode}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'opd card to excel';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $mode = $this->argument('mode');
        $filename = storage_path("app/temp/pinyo-opd-card-{$mode}.xlsx");

        function visitGeneratorFull() {
            foreach (Visit::with('patient')
                ->with(['vaccinations' => fn ($q) => $q->select(['vaccinated_at', 'brand_id', 'dose_no', 'patient_id'])->orderBy('dose_no')])
                ->whereBetween('date_visit', ['2022-03-01', '2022-08-31'])
                ->where('status', 4)
                ->orderBy('discharged_at')
                ->cursor() as $visit) {
                    yield (new OPDCardExportManager)->getData($visit);
            }
        }

        function visitGeneratorFiltered() {
            foreach (Visit::with('patient')
                ->with(['vaccinations' => fn ($q) => $q->select(['vaccinated_at', 'brand_id', 'dose_no', 'patient_id'])->orderBy('dose_no')])
                ->whereBetween('date_visit', ['2022-03-01', '2022-08-31'])
                ->where('status', 4)
                ->orderBy('discharged_at')
                ->cursor() as $visit) {
                    $manager = new OPDCardExportManager;
                    $data = $manager->getData($visit);
                    if (
                        $data['covid_19_infection_by_positive_atk'] === 'YES'
                        || $data['np_swab_result'] === 'Detected'
                    ) {
                        yield $manager->getData($visit);
                    }
            }
        }
        if ($mode === 'full') {
            (new FastExcel(visitGeneratorFull()))->export($filename);
        } else {
            (new FastExcel(visitGeneratorFiltered()))->export($filename);
        }
        return 0;
    }
}
