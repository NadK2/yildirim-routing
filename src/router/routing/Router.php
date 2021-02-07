<?php

namespace Yildirim\Routing;

use Yildirim\Routing\Exceptions\MethodNotAllowedException;
use Yildirim\Routing\Exceptions\MiddlewareException;
use Yildirim\Routing\Exceptions\RouteNotFoundException;
use Yildirim\Routing\Middleware\CorsMiddleware;

/**
 *
 */
class Router
{

    /**
     * controllerNamespace
     *
     * @var string
     */
    private static $controllerNamespace = '';

    /**
     * middlewareNamespace
     *
     * @var string
     */
    private static $middlewareNamespace = '';

    /**
     * csrfEnabled
     *
     * @var bool
     */
    public static $csrfEnabled = false;

    /**
     * allowedDomain for Cors.
     *
     * @var string
     */
    private static $allowedDomain = '';

    /**
     * processRequest
     *
     * @return Response
     */
    public static function processRequest()
    {

        //get the matching route.
        $route = self::resolveRoute();

        if (!$route) {
            throw new RouteNotFoundException("Route [ '" . request()->uri() . "' ] Not Found");
            return;
        }

        //process route with middleware.
        $response = self::processRequestWithMiddleware($route);

        return $response;

    }

    /**
     * processRequestWithMiddleware
     *
     * @param  mixed $route
     * @return void
     */
    private static function processRequestWithMiddleware($route)
    {

        $middleware = [];
        foreach ($route->middleware ?? [] as $m) {
            $m = new $m();

            if (!method_exists($m, 'handle')) {
                throw new MiddlewareException("Middleware [ " . get_class($m) . " ] is missing [ handle ] method");
                return;
            }

            $middleware[] = $m;
        }

        //add middleware around the route handler.
        $routeWithMiddleware = array_reduce(array_reverse($middleware) ?? [], function ($next, $current) {
            return self::createMiddleware($next, $current);
        }, function ($request) use ($route) {

            app()->setInstance('request', $request);

            $response = self::invokeRouteHandler($route, $request);

            //check if the response is a Response Object.
            $responseClass = app()->has('response') ? get_class(app('response', [''])) : Response::class;
            if ($response instanceof $responseClass) {
                return $response;
            }

            //return the response
            if (app()->has('response')) {
                return app('response', [$response]);
            }

            return new Response($response);

        });

        //execute middleware and route.
        return $routeWithMiddleware(request());
    }

    /**
     * invokeRouteHandler
     *
     * @param  mixed $route
     * @return mixed string|object
     */
    private static function invokeRouteHandler($route, $request)
    {
        $params = $request->route()->parameters();

        if (isset($route->callable)) {

            $response = app()->resolveFunction($route->callable, array_values($params));

        } else {

            $controller = app()->get($route->controller);

            $response = app()->resolveMethod($controller, $route->function, array_values($params));
        }

        return $response;
    }

    /**
     * setControllerNamespace
     *
     * @param  mixed $namespace
     * @return void
     */
    public static function setControllerNamespace($namespace)
    {
        self::$controllerNamespace = rtrim($namespace, '\\') . '\\';
    }

    /**
     * getControllerNamespace
     *
     * @return string
     */
    public static function getControllerNamespace()
    {
        return self::$controllerNamespace;
    }

    /**
     * setMiddlewareNamespace
     *
     * @param  mixed $namespace
     * @return void
     */
    public static function setMiddlewareNamespace($namespace)
    {
        self::$middlewareNamespace = rtrim($namespace, '\\') . '\\';
    }

    /**
     * getControllerNamespace
     *
     * @return string
     */
    public static function getMiddlewareNamespace()
    {
        return self::$middlewareNamespace;
    }

    /**
     * resolveRoute
     *
     * @return mixed Path|bool
     */
    public static function resolveRoute()
    {
        //the user requested url.
        $path = new Path(['uri' => request()->uri()]);

        //get routes that match requested url.
        $matches = self::getAllMatchingRoutes($path);

        //return an exact method and url match.
        if ($route = self::getMatchingRoute($matches, $path)) {
            return $route;
        }

        if (count($matches)) {
            //if options route then return correct headers. with blank response.

            if ($route = self::isOptionsRequest($matches, $path)) {
                return $route;
            }

            //if route matches but no method match.
            throw new MethodNotAllowedException("Method [ '" . request()->method() . "' ] is not allowed for route [ '" . request()->uri() . "' ]");
        }

        return false;
    }

    /**
     * createLayer
     *
     * @param  mixed $nextLayer
     * @param  mixed $layer
     * @return void
     */
    private static function createMiddleware($next, $middleware)
    {
        return function ($request) use ($next, $middleware) {
            return $middleware->handle($request, $next);
        };
    }

    /**
     * getAllMatchingRoutes
     *
     * @param  mixed $path
     * @return array
     */
    private static function getAllMatchingRoutes($path)
    {
        $matches = [];

        foreach (Route::getRouteList() as $route) {

            if (RouteMatcher::matches($route, $path)) {
                $matches[] = $route;
            }

        }

        return $matches;
    }

    /**
     * getMatchingRoute
     *
     * @param  mixed $matches
     * @param  mixed $path
     * @return mixed
     */
    private static function getMatchingRoute($matches, $path)
    {
        foreach ($matches as $route) {

            if ($route->method == "ANY" || $route->method == request()->method()) {
                app()->setInstance(RequestRoute::class, new RequestRoute($route, $path));
                return $route;
            }

        }

        return false;
    }

    /**
     * isOptionsRequest
     *
     * @param  mixed $route
     * @param  mixed $path
     * @return mixed
     */
    private static function isOptionsRequest($routes, $path)
    {
        $methods = collect($routes)->map(function ($r) {
            return $r->method;
        });

        if (strtolower(request()->method()) == 'options') {

            //get allowed methods for requested route.
            session()->put('allowed-methods', $methods->implode(", "));

            // add the cors middleware.
            $routes[0]->middleware = $routes[0]->middleware ?? [];
            $routes[0]->middleware[] = CorsMiddleware::class;

            // replace current handler and return empty response.
            unset($routes[0]->function);
            $routes[0]->callable = function () {
                return '';
            };

            app()->setInstance(RequestRoute::class, new RequestRoute($routes[0], $path));
            return $routes[0];

        }

        return false;
    }

    /**
     * enableCsrf
     *
     * @param  mixed $bool
     * @return void
     */
    public static function enableCsrf(bool $bool)
    {
        self::$csrfEnabled = $bool;
    }

    /**
     * allowedDomain
     *
     * @return void
     */
    public static function setAllowedOrigin($domain)
    {
        self::$allowedDomain = $domain;
    }

    /**
     * allowedDomain
     *
     * @return void
     */
    public static function allowedOrigin()
    {
        return self::$allowedDomain;
    }

}
