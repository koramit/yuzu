<?php

namespace App\Http\Controllers;

use App\Managers\MocktailManager;
use App\Managers\VisitManager;
use App\Models\Visit;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\Session;
use Inertia\Inertia;

class VisitDecisionController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $manager = new VisitManager();
        $flash = $manager->getFlash($user);
        $flash['main-menu-links'][] = ['icon' => 'file-excel', 'label' => 'Export Excel', 'route' => 'export.decisions', 'can' => $user->can('view_decision_list'), 'use_a_tag' => true];
        $flash['page-title'] = 'Decision';
        $manager->setFlash($flash);
        $manager = new MocktailManager();
        $dateVisit = Request::input('date_visit', now('asia/bangkok')->format('Y-m-d'));
        Session::put('positive-decision-export-date', $dateVisit);
        $positiveCases = Visit::with('patient')
                              ->where('swabbed', true)
                              ->whereDateVisit($dateVisit)
                              ->where('form->management->np_swab_result', 'Detected')
                              ->get()
                              ->transform(function ($visit) use ($manager, $user) {
                                  $positive = $manager->getReferCase($visit);
                                  $positive['can'] = [
                                      'refer' => $user->can('refer'),
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
            if (($visit->form['decision'] ?? null) && $mocktailOptions->contains($visit->form['decision']['refer_to'])) { // cancel mocktail
                $response = Http::acceptJson()
                            ->timeout(5)
                            ->withToken($user->mocktail_token)
                            ->post(config('services.mocktail.refer_case_endpoint'), ['hn' => Request::input('hn'), 'refer_type' => 'cancel']);
                $decision['linked'] = $response->successful();
            }
        } else { // call mocktail
            $data = Request::all();
            $data['refer_type'] = $data['refer_to'] === 'HI' ? 'Home Isolation' : 'Hospitel';
            $data['refer_to'] = $data['refer_to'] === 'HI' ? 'Home Isolation' : $data['refer_to'];
            $response = Http::acceptJson()
                            ->timeout(2)
                            ->retry(3, 100)
                            ->withToken($user->mocktail_token)
                            ->post(config('services.mocktail.refer_case_endpoint'), $data);
            $decision['linked'] = $response->successful();
        }
        $visit->forceFill(['form->decision' => $decision])->save();
        $visit->actions()->create(['action' => 'link_mocktail', 'user_id' => $user->id]);

        return ['linked' => $decision['linked']];
    }
}
