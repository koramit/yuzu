<?php

namespace App\Http\Controllers;

use App\Events\VisitUpdated;
use App\Models\Visit;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Imagick;

class WonderWomenController extends Controller
{
    public function show(Visit $visit)
    {
        if (! $visit->form['management']['screenshot']) {
            return '👩‍🔬';
        }

        return Response::file(storage_path('app/'.$visit->form['management']['screenshot']));
    }

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

        $today = now();
        $todayStr = $today->format('d-m-y');
        $dateAfterStr = now()->addDays(-1)->format('d/m/Y');

        $visits = Visit::with('patient')
                       ->whereDateVisit($today->format('Y-m-d'))
                       ->whereStatus(4) // discharged
                       ->where('form->management->np_swab', true)
                       ->whereNull('form->management->np_swab_result')
                       ->orderBy('discharged_at')
                       ->get()
                       ->transform(function ($visit) use ($todayStr, $dateAfterStr) {
                           return [
                                'hn' => $visit->hn,
                                'slug' => $visit->slug,
                                'date' => $todayStr,
                                'date_after' => $dateAfterStr,
                                'result' => null,
                                'note' => null,
                                'retry' => 0,
                                'type' => $visit->patient_type,
                            ];
                       });

        return $visits;
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
                'slug' => 'required',
                'result' => 'required',
                'screenshot' => 'file',
        ]);

        $visit = Visit::whereSlug(Request::input('slug'))
                      ->first();

        if (! $visit) {
            abort(422);
        }

        if (Request::has('screenshot')) {
            $path = Request::file('screenshot')->store('temp');
            $newPath = $this->trimCroissant($path);
            Storage::delete($path);
            $path = $newPath;
        } else {
            $path = null;
        }

        $visit->forceFill([
            'form->management->np_swab_result' => Request::input('result') !== 'timeout' ? Request::input('result') : null,
            'form->management->np_swab_result_note' => Request::input('note'),
            'form->management->screenshot' => $path,
        ])->save();

        VisitUpdated::dispatch($visit);

        Cache::put('croissant-message', 'reported');
        Cache::put('croissant-latest', now());
        Cache::put('croissant-pending-hits', 0);

        return ['ok' => true];
    }

    public function update()
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
            'slug' => 'required',
            'message' => 'required',
        ]);

        $visit = Visit::whereSlug(Request::input('slug'))
                      ->first();

        if (! $visit) {
            abort(422);
        }

        $message = Request::input('message');
        Cache::put('croissant-message', $message);
        Cache::put('croissant-latest', now());

        if ($message === 'not found') {
            $notFound = Cache::get('croissant-not-found', []);
            $notFound[] = $visit->slug;
            Cache::push('croissant-not-found', collect($notFound)->unique()->values()->all());
        } else { // result to follow
            Cache::increment('croissant-pending-hits');
        }

        return ['ok' => true];
    }

    public function feedback()
    {
        return [
            'message' => Cache::get('croissant-message'),
            'updated_at' => Cache::get('croissant-latest')->diffForHumans(now()),
            'not_found' => Cache::get('croissant-not_found'),
            'pending_hits' => Cache::get('croissant-pending-hits'),
        ];
    }

    protected function trimCroissant($path)
    {
        $xOffset = 130;
        $image = new Imagick(storage_path('app/'.$path));
        $width = $image->getImageWidth();
        $height = $image->getImageHeight();
        $image->cropImage($width - $xOffset, $height, $xOffset, 0);
        $path = 'croissant/'.Str::uuid()->toString().'.png';

        if (Storage::put($path, $image->getImageBlob())) {
            return $path;
        } else {
            return false;
        }
    }
}
