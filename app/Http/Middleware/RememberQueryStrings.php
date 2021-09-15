<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class RememberQueryStrings
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        if ($request->wantsJson()) {
            return $next($request);
        }

        if (empty($request->all())) {
            return $this->remembered($next, $request);
        }

        if ($request->get('remember') === 'no') {
            return $next($request);
        }

        if ($request->get('remember') === 'forget') {
            return $this->forget($next, $request);
        }

        return $this->remember($next, $request);
    }

    protected function remembered($next, $request)
    {
        $remembered = array_filter($request->session()->get('remember_query_strings.'.$request->route()->getName()) ?? []);

        logger('recall');
        logger('remember_query_strings.'.$request->route()->getName().' => '.http_build_query($remembered));

        if ($remembered) {
            $request->session()->reflash();

            logger('remembered.redirect');
            logger(url($request->path()).'?'.http_build_query($remembered));

            return redirect(url($request->path()).'?'.http_build_query($remembered));
        }

        return $next($request);
    }

    protected function remember($next, $request)
    {
        $request->session()->put('remember_query_strings.'.$request->route()->getName(), array_filter($request->all()));

        logger('remember');
        logger('remember_query_strings.'.$request->route()->getName().' => '.http_build_query(array_filter($request->all())));

        return $next($request);
    }

    protected function forget($next, $request)
    {
        $request->session()->remove('remember_query_strings.'.$request->route()->getName());

        return redirect(url($request->path()));
    }
}
