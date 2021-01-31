<?php

namespace Yildirim\Routing\Middleware;

use Yildirim\Interfaces\Middleware;

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
            http_response_code(403);
            throwException('CsrfException', 'csrf token mismatch', 403);
        }

        return $next($request);
    }

}
