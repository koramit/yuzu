<?php

namespace App\Http\Controllers;

use App\Events\VisitUpdated;
use App\Managers\VisitManager;
use App\Models\Visit;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Request;

class VisitDischargeListController extends Controller
{
    public function store(Visit $visit)
    {
        $data = Request::all();
        $user = Auth::user();
        $manager = new VisitManager();
        $errors = $manager->validateDischarge($data);

        $manager->saveVisit($visit, $data, $user);

        if (count($errors)) {
            return back()->withErrors($errors);
        }

        if ($user->hasRole('md')) {
            $route = 'visits.exam-list';
        } else {
            $route = $visit->status_index_route;
        }

        // discharge by md from exam
        $visit->status = 'discharged';
        $visit->discharged_at = now();
        $visit->forceFill([
            'form->md->name' => $user->profile['full_name'],
            'form->md->pln' => $user->profile['pln'],
            'form->md->signed_on_behalf' => false,
            'form->md->signed_at' => now(),
        ]);
        $visit->save();

        $visit->actions()->createMany([
            ['action' => 'sign_opd_card', 'user_id' => $user->id],
            ['action' => 'discharge', 'user_id' => $user->id],
        ]);

        VisitUpdated::dispatch($visit);

        return Redirect::route($route)->with('messages', [
            'status' => 'success',
            'messages' => [
                'จำหน่าย '.$visit->title.' สำเร็จ',
            ],
        ]);
    }

    public function update(Visit $visit)
    {
        $visit->status = 'discharged';
        $visit->discharged_at = now();
        if ($visit->form['management']['np_swab']
            && $visit->form['management']['container_no']
        ) {
            if (Request::input('swabbed') === true) {
                $visit->swabbed = true;
                $visit->forceFill([
                    'form->management->on_hold' => false,
                    'form->management->container_no' => 0,
                ]);
            } else {
                $visit->swabbed = ! $visit->form['management']['on_hold'];
            }
        }
        $visit->save();
        $visit->actions()->create(['action' => 'discharge', 'user_id' => Auth::id()]);
        VisitUpdated::dispatch($visit);

        return Redirect::back();
    }
}
