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
        if (!$request->has('csrf_token')) {
            throwException('CsrfException', 'csrf token mismatch', 403);
        }

        if ($request->csrf_token != session('post_csrf')) {
            throwException('CsrfException', 'csrf token mismatch', 403);
        }

        return $request;
    }

}
