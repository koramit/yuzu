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
                       ->where('swabbed', true)
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
                                'specimen_no' => $visit->specimen_no,
                            ];
                       });

        Cache::put('croissant-message', 'fetch');
        Cache::put('croissant-not-found', []);
        Cache::put('croissant-pending-hits', 0);
        Cache::put('croissant-report-hits', 0);
        Cache::put('croissant-not-found-hits', 0);

        if (Request::input('mode', 2) === 2) {
            return $visits;
        }

        return collect($visits)->filter(fn ($visit) => $visit['specimen_no'] % 2 === Request::input('mode'))->values()->all();
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

        Cache::increment('croissant-report-hits');
        Cache::put('croissant-message', 'reported');
        Cache::put('croissant-latest', now());
        Cache::put('croissant-pending-hits', 0);
        Cache::put('croissant-not-found-hits', 0);
        $this->resolveNotFound($visit->slug);

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
            Cache::put('croissant-not-found', collect($notFound)->unique()->values()->all());
            Cache::increment('croissant-not-found-hits');
            Cache::put('croissant-pending-hits', 0);
        } else { // result to follow
            Cache::increment('croissant-pending-hits');
            Cache::put('croissant-not-found-hits', 0);
            $this->resolveNotFound($visit->slug);
        }

        Cache::put('croissant-report-hits', 0);

        return ['ok' => true];
    }

    public function feedback()
    {
        return [
            'message' => Cache::get('croissant-message'),
            'updated_at' => Cache::get('croissant-latest')->diffForHumans(now()),
            'pending_hits' => Cache::get('croissant-pending-hits'),
            'not_found_hits' => Cache::get('croissant-not-found-hits'),
            'report_hits' => Cache::get('croissant-report-hits'),
            'not_found' => Cache::get('croissant-not-found'),
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

    protected function resolveNotFound($slug)
    {
        $notFound = Cache::get('croissant-not-found', []);
        $resolved = collect($notFound)
                        ->filter(function ($value) use ($slug) {
                            return $value !== $slug;
                        })->values()
                        ->all();
        Cache::put('croissant-not-found', $resolved);
    }
}
