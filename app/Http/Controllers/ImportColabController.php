<?php

namespace App\Http\Controllers;

use App\Models\Visit;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Request;
use Rap2hpoutre\FastExcel\Facades\FastExcel;

class ImportColabController extends Controller
{
    public function __invoke()
    {
        $path = Request::file('file')->store('uploads');
        $colabs = FastExcel::import(storage_path('app/'.$path));
        $visits = Visit::with('patient')->get();
        foreach ($visits as $visit) {
            $index = $colabs->search(function ($lab) use ($visit) {
                return $lab['HN'] === $visit->hn;
            });

            if ($index !== false) {
                $visit->forceFill([
                    'form->result' => $colabs[$index]['RESULT'],
                ]);
                $visit->save();
            }
        }

        return Redirect::back();
    }
}
