<?php

namespace App\Http\Controllers;

use App\Models\Visit;
use App\Models\VisitAction;

class VisitActionsController extends Controller
{
    public function transactions(Visit $visit)
    {
        $actions = VisitAction::with('user')
                              ->whereVisitId($visit->id)
                              ->where('action', '<>', 'view')
                              ->where('created_at', '<', $visit->date_visit->addDay()->format('Y-m-d'))
                              ->orderBy('id')
                              ->get()
                              ->transform(function ($action) {
                                  return [
                                    'action' => $action->action_label,
                                    'user' => $action->user->name,
                                    'date' => $action->created_at->tz('asia/bangkok')->format('d M Y'),
                                    'time' => $action->created_at->tz('asia/bangkok')->format('H:i:s'),
                                ];
                              });

        return $actions;
    }
}
