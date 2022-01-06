<?php

namespace App\Http\Controllers\Services;

use App\Http\Controllers\Controller;

class LINEWebhooksController extends Controller
{
    public function __invoke($token)
    {
        if ($token !== config('services.line.bot_token')) {
            abort(404);
        }

        return ['ok' => true];
    }
}
