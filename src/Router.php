<?php

namespace Yildirim\Routing;

use Yildirim\Classes\Request;

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
     * processRequest
     *
     * @return Response
     */
    public static function processRequest()
    {

        //get the matching route.
        $route = self::resolveRoute();

        if (!$route) {
            throwException('RouteException', "Page [" . request()->uri() . "] not found", 404);
        }

        //run route middleware.
        self::runMiddleware($route);

        //execute the route function.
        $response = self::invokeRouteHandler($route);

        //return the response
        if (app()->has('response')) {
            return app('response', [$response]);
        }

        return new Response($response);
    }

    /**
     * runMiddleware
     *
     * @param  mixed $route
     * @return void
     */
    private static function runMiddleware($route)
    {
        //get the Request Instance
        $request = request();

        foreach ($route->middleware ?? [] as $m) {
            $middleware = new $m();

            if (!method_exists($middleware, 'run')) {
                throwException('MiddlewareException', "Middleware [ $m ] is missing [ run ] method");
            }

            $request = $middleware->run($request);

            if (!$request instanceof Request) {
                throwException('MiddlewareException', "Middleware [ $m ] method [ run ] must return an instance of [ " . get_class(request()) . " ]");
            }
        }

        //replace Request with updated instance.
        app()->setInstance('request', $request);

        return;
    }

    /**
     * invokeRouteHandler
     *
     * @param  mixed $route
     * @return mixed string|object
     */
    private static function invokeRouteHandler($route)
    {
        $params = request()->getRouteParameters();

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
                    throwException('MethodNotAllowed', "Method [ '" . request()->method() . "' ] is not allowed for route [ '" . request()->uri() . "' ]", 405);
                }

                request()->setRouteParameters($route->getSlugValues($path));

                return $route;
            }
        }

        return false;
    }

}
