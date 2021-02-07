<?php

namespace Yildirim\Routing;

use Yildirim\Routing\Exceptions\RouteException;
use Yildirim\Routing\Middleware\PostMiddleware;
use Yildirim\Routing\Middleware\PutMiddleware;

/**
 * Route
 */
class RouteBuilder
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
     *
     */
    private static $regex = [];

    /**
     *
     */
    private static $groupRegex = [];

    /**
     *
     */
    private static $globalMiddleware = [];

    /**
     *
     */
    private static $groupName = [];

    /**
     *
     */
    private static $name = [];

    /**
     *
     */
    private static $csrfEnabled = false;

    /**
     * any
     *
     * @param  mixed $uri
     * @param  mixed $handler
     * @return void
     */
    public static function any($uri, $handler)
    {
        return self::addRoute('ANY', $uri, $handler);
    }

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
     * options
     *
     * @param  mixed $uri
     * @param  mixed $handler
     * @return void
     */
    public static function options($uri, $handler)
    {
        return self::addRoute('OPTIONS', $uri, $handler);
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
        if (Router::$csrfEnabled) {
            self::middleware(PostMiddleware::class);
        }

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
        self::middleware(PutMiddleware::class);
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
        self::middleware(PutMiddleware::class);
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
     * resource
     *
     * @return void
     */
    public static function resource($uri, $handler)
    {
        return self::group($uri, function () use ($handler) {
            self::get("", $handler . "@index");
            self::post("", $handler . "@create");
            self::get('/{id}', $handler . "@show");
            self::post('/{id}', $handler . "@update");
            self::delete('/{id}', $handler . "@destroy");
        });
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
            throw new RouteException("Route [" . self::getRouteKey($attributes) . "] has already been defined");
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

        self::$groupRegex = array_merge(self::$groupRegex, self::$regex);
        $newRegex = self::$regex ? true : false;
        self::$regex = [];

        self::$groupName = array_merge(self::$groupName, self::$name);
        $newName = self::$name ? true : false;
        self::$name = [];

        //invoke group callback.
        $callback();

        array_pop(self::$group);

        if ($newMiddleware) {
            array_pop(self::$groupMiddleware);
        }

        if ($newRegex) {
            array_pop(self::$groupRegex);
        }

        if ($newName) {
            array_pop(self::$groupName);
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

        self::$middleware = array_merge(self::$middleware, $middleware);

        return new static;
    }

    /**
     * match
     *
     * @param  mixed $slug
     * @param  mixed $regex
     * @return static
     */
    public static function match($slug, $regex = null)
    {
        $regex = is_array($slug) ? $slug : [$slug => $regex];

        self::$regex = $regex;

        return new static;
    }

    /**
     * name
     *
     * @param  mixed $name
     * @return static
     */
    public static function name($name)
    {

        self::$name[] = $name;

        return new static;
    }

    /**
     * globalMiddleware
     *
     * @param  mixed $middleware
     * @param  mixed $clearPrevious
     * @return void
     */
    public static function globalMiddleware($middleware, $clearPrevious = false)
    {
        $middleware = is_array($middleware) ? $middleware : [$middleware];

        if ($clearPrevious) {
            self::$globalMiddleware = [];
        }

        self::$globalMiddleware = array_merge(self::$globalMiddleware, $middleware);

    }

    /**
     * clearGlobalMiddleware
     *
     * @return void
     */
    public static function clearGlobalMiddleware()
    {
        return self::$globalMiddleware = [];
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
        return '/' . trim(implode("/", $url), "/");
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
                throw new RouteException("Controller [ $attributes[controller] ] for route [ $attributes[uri] ] is not resolvable");
            }

            if (!$controller->hasMethod($attributes['function'])) {
                throw new RouteException("Method [ $attributes[controller]@$attributes[function] ] for route [ $attributes[uri] ] is not resolvable");
            }

        } else {
            //not recognised.
            throw new RouteException("handler ['$handler'] for route [ $attributes[uri] ] is not resolvable");
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

        self::setRegex($route);

        self::setName($route);

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

        if (self::$globalMiddleware) {

            $route->middleware(self::$globalMiddleware);

        }

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
     * setRegex
     *
     * @param  mixed $route
     * @return void
     */
    private static function setRegex($route)
    {

        if (self::$groupRegex) {

            $route->match(self::$groupRegex);

        }

        if (self::$regex) {

            $route->match(self::$regex);

            self::$regex = [];

        }

        return;
    }

    /**
     * setName
     *
     * @param  mixed $route
     * @return void
     */
    private static function setName($route)
    {

        if (self::$groupName) {

            $route->name(implode("", self::$groupName));

        }

        if (self::$name) {

            $route->name(implode("", self::$name));

            self::$name = [];

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
                throw new RouteException('Route [ ' . $route->uri . ' ]  optional slug [ ' . $slug->value . ' ] is missing default value in handler');
            }
        }

    }

    /**
     * reverseRouteLookup
     *
     * @param  string $name
     * @param  array $paramters
     * @return string
     */
    public static function reverseRouteLookup($name, $parameters = [])
    {
        $path = collect(self::$routes)->first(function ($route) use ($name) {
            return $name == $route->name;
        });

        if (!$path) {
            throw new RouteException("Route with name [ $name ] not found");
        }

        //has no slugs.
        if (!$path->slugs()->count()) {
            return $path->uri;
        }

        $uri = [];

        foreach ($path->parameters->toArray() as $param) {

            if ($param->id) {
                if (!isset($parameters[$param->id])) {
                    throw new RouteException('Route is missing [ ' . $param->id . ' ] argument');
                }
                $uri[] = $parameters[$param->id];
            } else {
                $uri[] = $param->value;
            }
        }

        return implode("/", $uri);

    }

}
