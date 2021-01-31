<?php

namespace Yildirim\Interfaces;

interface Middleware
{

    /**
     * handle
     *
     * @param  mixed $request
     * @param  mixed $next
     * @return mixed
     */
    public function handle($request, $next);
}
