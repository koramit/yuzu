<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ServerSendEventsController extends Controller
{
    public function __invoke()
    {
        $response = new StreamedResponse();
        $response->setCallback(function () {
            $channel = Request::input('channel');
            echo 'data: '.json_encode(['updatestamp' => Cache::get($channel.'-list-new', 0)])."\n\n";
            ob_flush();
            flush();
            usleep(500000);
        });

        $response->headers->set('Content-Type', 'text/event-stream');
        $response->headers->set('X-Accel-Buffering', 'no');
        $response->headers->set('Cach-Control', 'no-cache');
        $response->send();
    }
}
