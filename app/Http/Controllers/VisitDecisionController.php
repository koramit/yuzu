<?php

namespace App\Http\Controllers;

use App\Managers\MocktailManager;
use App\Managers\VisitManager;
use App\Models\Visit;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Request;
use Inertia\Inertia;

class VisitDecisionController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $manager = new VisitManager();
        $flash = $manager->getFlash($user);
        $flash['page-title'] = 'Decision';
        $manager->setFlash($flash);
        $manager = new MocktailManager();
        $dateVisit = Request::input('date_visit', now('asia/bangkok')->format('Y-m-d'));
        $positiveCases = Visit::with('patient')
                              ->where('swabbed', true)
                              ->whereDateVisit($dateVisit)
                              ->where('form->management->np_swab_result', 'Detected')
                              ->get()
                              ->transform(function ($visit) use ($manager, $user) {
                                  $positive = $manager->getReferCase($visit);
                                  $positive['can'] = [
                                      'evaluate' => $user->can('evaluate'),
                                  ];

                                  return $positive;
                              });

        return Inertia::render('Decisions/Index', [
            'positiveCases' => $positiveCases,
            'dateVisit' => $dateVisit,
            'referToOptions' => ['Ward', 'Baiyoke', 'Riverside', 'HI', 'Colink', 'อื่นๆ'],
        ]);
    }

    public function update(Visit $visit)
    {
        Request::validate([
            'refer_to' => 'required|string',
            'date_refer' => 'required|date',
        ]);

        $user = Auth::user();
        $decision = Request::only(['refer_to', 'date_refer', 'remark']);
        $mocktailOptions = collect(['Baiyoke', 'Riverside', 'HI']);

        if (! $mocktailOptions->contains(Request::input('refer_to'))) {
            $decision['linked'] = true;
        } else { // call mocktail
            $response = Http::acceptJson()
                            ->timeout(5)
                            ->withToken($user->mocktail_token)
                            ->post(config('services.mocktail.refer_case_endpoint'), Request::all());
            $decision['linked'] = $response->successful();
        }
        $visit->forceFill(['form->decision' => $decision])->save();
        $visit->actions()->create(['action' => 'link_mocktail', 'user_id' => $user->id]);

        return ['linked' => $decision['linked']];
    }
}
