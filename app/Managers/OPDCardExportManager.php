<?php

namespace App\Managers;

use App\Models\Visit;
use App\Traits\OPDCardExportable;

class OPDCardExportManager
{
    use OPDCardExportable;

    public function getData(Visit $visit)
    {
        return $this->allData($visit);
    }
}