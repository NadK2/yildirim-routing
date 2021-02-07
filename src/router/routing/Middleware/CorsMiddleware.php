<?php

namespace Yildirim\Routing\Middleware;

use Yildirim\Interfaces\Middleware;
use Yildirim\Routing\Request;
use Yildirim\Routing\Router;

class CorsMiddleware implements Middleware
{

    /**
     * run
     *
     * @param  mixed $request
     * @return void
     */
    public function handle($request, $next)
    {
        $response = $next($request);

        return $response->setCode(204)->setHeaders([
            'Access-Control-Allow-Methods' => session()->pull('allowed-methods', ''),
            'Access-Control-Allow-Headers' => '*',
            'Access-Control-Allow-Origin' => Router::allowedOrigin(),
        ]);

    }

}
