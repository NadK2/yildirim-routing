<?php

namespace Yildirim\Routing\Middleware;

class PostMiddleware
{

    /**
     * run
     *
     * @param  mixed $request
     * @return void
     */
    public function run($request)
    {
        if (!$request->has('csrf_token') || $request->csrf_token != session('post_csrf')) {
            error(403);
            // throwException('CsrfException', 'csrf token mismatch', 403);
        }

        return $request;
    }

}
