<?php

namespace App\Http\Controllers\Services;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Request;

class LINEWebhooksController extends Controller
{
    public function __invoke($token)
    {
        $hash = hash_hmac('sha256', Request::getContent(), config('services.line.bot_secret'), true);
        $signature = base64_encode($hash);

        if ($signature !== Request::header('x-line-signature')) {
            abort(404);
        }

        return ['ok' => true];
    }
}
