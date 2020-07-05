<?php

namespace Yildirim\Routing;

/**
 * Route
 */
class Route
{
    /**
     *
     */
    private static $group = [];

    /**
     *
     */
    private static $routes = [];

    /**
     *
     */
    private static $middleware = [];

    /**
     *
     */
    private static $groupMiddleware = [];

    /**
     * get
     *
     * @param  mixed $uri
     * @param  mixed $handler
     * @return Path
     */
    public static function get($uri, $handler)
    {
        return self::addRoute('GET', $uri, $handler);
    }

    /**
     * post
     *
     * @param  mixed $uri
     * @param  mixed $handler
     * @return Path
     */
    public static function post($uri, $handler)
    {
        return self::addRoute('POST', $uri, $handler);
    }

    /**
     * put
     *
     * @param  mixed $uri
     * @param  mixed $handler
     * @return Path
     */
    public static function put($uri, $handler)
    {
        return self::addRoute('PUT', $uri, $handler);
    }

    /**
     * patch
     *
     * @param  mixed $uri
     * @param  mixed $handler
     * @return Path
     */
    public static function patch($uri, $handler)
    {
        return self::addRoute('PATCH', $uri, $handler);
    }

    /**
     * delete
     *
     * @param  mixed $uri
     * @param  mixed $handler
     * @return void
     */
    public static function delete($uri, $handler)
    {
        return self::addRoute('DELETE', $uri, $handler);
    }

    /**
     * addRoute
     *
     * @param  mixed $method
     * @param  mixed $uri
     * @param  mixed $handler
     * @return void
     */
    private static function addRoute($method, $uri, $handler)
    {
        $attributes = self::buildRouteAttributes($uri, $method, $handler);

        if (isset(self::$routes[self::getRouteKey($attributes)])) {
            throwException('RouteException', "Route [" . self::getRouteKey($attributes) . "] has already been defined");
        }

        $route = self::createPathWithAttributes($attributes);
        self::$routes[self::getRouteKey($attributes)] = $route;

        return $route;
    }

    /**
     * group
     *
     * @param  mixed $uri
     * @return void
     */
    public static function group(...$args)
    {
        $uri = null;
        $callback = null;

        if (count($args) == 1 && is_callable($args[0])) {
            $callback = $args[0];
        } elseif (count($args) == 2) {
            $uri = $args[0];
            $callback = $args[1];
        }

        if (!is_null($uri)) {
            self::$group[] = trim($uri, "/");
        }

        self::$groupMiddleware = array_merge(self::$groupMiddleware, self::$middleware);
        $newMiddleware = self::$middleware ? true : false;
        self::$middleware = [];

        //invoke group callback.
        $callback();

        array_pop(self::$group);

        if ($newMiddleware) {
            array_pop(self::$groupMiddleware);
        }

        return;
    }

    /**
     * getRouteList
     *
     * @return array
     */
    public static function getRouteList()
    {
        return self::$routes;
    }

    /**
     * middleware
     *
     * @param  mixed $middleware
     * @return Static
     */
    public static function middleware($middleware)
    {
        $middleware = is_array($middleware) ? $middleware : func_get_args();

        foreach ($middleware as $m) {
            if (!class_exists($m)) {
                throwException('MiddlewareException', "Middleware:['$m'] does not exist");
            }
        }

        self::$middleware = array_merge(self::$middleware, $middleware);

        return new static;
    }

    /**
     * parseControllerFunction
     *
     * @param  mixed $handler
     * @return array
     */
    private static function parseControllerFunction($handler)
    {
        return array_combine(['controller', 'function'], explode("@", $handler));
    }

    /**
     * cleanUri
     *
     * @param  mixed $uri
     * @return string
     */
    private static function cleanUri($uri)
    {
        $url = [trim(implode("/", self::$group), "/"), trim($uri, "/")];

        if (!self::$group) {

            array_shift($url);

        } elseif (!trim($uri, "/")) {

            array_pop($url);
        }

        return '/' . implode("/", $url);
    }

    /**
     * buildRouteAttributes
     *
     * @param  mixed $url
     * @param  mixed $method
     * @param  mixed $handler
     * @return array
     */
    private static function buildRouteAttributes($url, $method, $handler)
    {

        $attributes = [
            'uri' => self::cleanUri($url),
            'method' => $method,
        ];

        //Closure
        if (is_callable($handler)) {

            $attributes['callable'] = $handler;

        } // String Containing Controller@function
        elseif (count(explode("@", $handler)) == 2) {

            $data = self::parseControllerFunction($handler);
            $attributes['controller'] = Router::getControllerNamespace() . $data['controller'];
            $attributes['function'] = $data['function'];

            if (class_exists($attributes['controller'])) {
                $controller = new \ReflectionClass($attributes['controller']);
            } else {
                throwException('RouteHandlerException', "Controller [ $attributes[controller] ] for route [ $attributes[uri] ] is not resolvable");
            }

            if (!$controller->hasMethod($attributes['function'])) {
                throwException('RouteHandlerException', "Method [ $attributes[controller]@$attributes[function] ] for route [ $attributes[uri] ] is not resolvable");
            }

        } else {
            //not recognised.
            throwException('RouteHandlerException', "handler ['$handler'] for route [ $attributes[uri] ] is not resolvable");
        }

        return $attributes;
    }

    /**
     * getRouteKey
     *
     * @param  mixed $attributes
     * @return string
     */
    private static function getRouteKey($attributes)
    {
        return $attributes['method'] . ': ' . $attributes['uri'];
    }

    /**
     * createPathWithAttributes
     *
     * @param  mixed $attributes
     * @return Path
     */
    private static function createPathWithAttributes($attributes)
    {
        $route = new Path($attributes);

        self::validate($route);

        self::setMiddleware($route);

        return $route;
    }

    /**
     * setMiddleware
     *
     * @param  mixed $route
     * @return void
     */
    private static function setMiddleware($route)
    {

        if (self::$groupMiddleware) {

            $route->middleware(self::$groupMiddleware);

        }

        if (self::$middleware) {

            $route->middleware(self::$middleware);

            self::$middleware = [];

        }

        return;
    }

    /**
     * validate
     *
     * @param  mixed $route
     * @return void
     */
    private static function validate($route)
    {

        if (isset($route->callable)) {

            self::resolveParameters(new \ReflectionFunction($route->callable), $route);

        } else {
            $controller = $route->controller;
            $controller = new $controller();

            self::resolveParameters(new \ReflectionMethod($controller, $route->function), $route);

        }
    }

    /**
     * resolveParameters
     *
     * @param  mixed $method
     * @param  mixed $params
     * @return void
     */
    private static function resolveParameters($method, Path $route)
    {
        // return;
        //checks if first parameter is Request and ignores it.
        $parameters = $method->getParameters();

        $pos = 0;
        foreach ($parameters as $position => $param) {

            if ($param->getClass() !== null) {
                continue;
            }

            $slug = $route->getSlugAtPosition($pos++);

            if ($slug && $slug->isOptional() && !$param->isDefaultValueAvailable()) {
                throwException('RouteHandlerException', 'Route [ ' . $route->uri . ' ]  optional slug [ ' . $slug->value . ' ] is missing default value in handler');
            }
        }

    }

}
