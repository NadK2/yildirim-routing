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
    public function handle($request)
    {
        if (!$request->has('csrf_token') || $request->csrf_token != session('post_csrf')) {
            http_response_code(403);
            throwException('CsrfException', 'csrf token mismatch', 403);
        }

        return $request;
    }

}
