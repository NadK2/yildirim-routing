<?php

namespace Yildirim\Routing\Middleware;

use Yildirim\Interfaces\Middleware;
use Yildirim\Routing\Exceptions\CsrfException;

class PostMiddleware implements Middleware
{

    /**
     * run
     *
     * @param  mixed $request
     * @return void
     */
    public function handle($request, $next)
    {
        if (!$request->has('csrf_token') || $request->csrf_token != session('post_csrf')) {
            throw new CsrfException();
        }

        return $next($request);
    }

}
