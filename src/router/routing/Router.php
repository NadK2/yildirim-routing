<?php

namespace Yildirim\Routing;

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
     * processRequest
     *
     * @return Response
     */
    public static function processRequest()
    {

        //get the matching route.
        $route = self::resolveRoute();

        if (!$route) {
            error(404);
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

            if (!method_exists($m, 'run')) {
                throwException('MiddlewareException', "Middleware [ " . get_class($m) . " ] is missing [ run ] method");
            }

            $middleware[] = $m;
        }

        //add middleware around the route handler.
        $routeWithMiddleware = array_reduce($middleware ?? [], function ($next, $current) {
            return self::createMiddleware($next, $current);
        }, function ($request) use ($route) {

            app()->setInstance('request', $request);

            $response = self::invokeRouteHandler($route, $request);

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

        foreach (Route::getRouteList() as $route) {

            if (RouteMatcher::matches($route, $path)) {

                if ($route->method != "ANY" && $route->method != request()->method()) {
                    error(405);
                    // throwException('MethodNotAllowed', "Method [ '" . request()->method() . "' ] is not allowed for route [ '" . request()->uri() . "' ]", 405);
                }

                app()->setInstance(RequestRoute::class, new RequestRoute($route, $path));

                return $route;
            }
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
            return $middleware->run($request, $next);
        };
    }
}
