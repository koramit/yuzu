<?php

namespace App\Http\Middleware;

use Illuminate\Http\Request;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that's loaded on the first page visit.
     *
     * @see https://inertiajs.com/server-side-setup#root-template
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Determines the current asset version.
     *
     * @see https://inertiajs.com/asset-versioning
     * @param  \Illuminate\Http\Request  $request
     * @return string|null
     */
    public function version(Request $request)
    {
        return parent::version($request);
    }

    /**
     * Defines the props that are shared by default.
     *
     * @see https://inertiajs.com/shared-data
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function share(Request $request)
    {
        return array_merge(parent::share($request), [
            'app' => [
                'baseUrl' => url(''),
                'today' => fn () => now('asia/bangkok')->format('Y-m-d'),
                'tomorrow_label' => fn () => now('asia/bangkok')->addDays(1)->format('d M Y'),
                'tomorrow' => fn () => now('asia/bangkok')->addDays(1)->format('Y-m-d'),
            ],
            'flash' => [
                'title' => fn () => $request->session()->pull('page-title', 'MISSING'),
                'messages' => fn () => $request->session()->pull('messages'),
                'mainMenuLinks' => fn () => $request->session()->pull('main-menu-links', []),
                'actionMenu' => fn () => $request->session()->pull('action-menu', []),
            ],
            'user' => fn () => $request->user()
                ? [
                    'id' => $request->user()->id,
                    'name' => $request->user()->name,
                    'roles' => $request->user()->role_names->toArray(),
                    'abilities' => $request->user()->abilities->toArray(),
                ] : null,
            'events' => [
                'confirmed_at' => null,
                'confirmed_reason' => null,
            ],
        ]);
    }
}
