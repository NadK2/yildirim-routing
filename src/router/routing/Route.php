<?php

namespace Yildirim\Routing;

/**
 * Route
 */
class Route
{

    /**
     * any
     *
     * @param  mixed $uri
     * @param  mixed $handler
     * @return void
     */
    public static function any($uri, $handler)
    {
        return RouteBuilder::any($uri, $handler);
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
        return RouteBuilder::get($uri, $handler);
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
        return RouteBuilder::options($uri, $handler);
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
        return RouteBuilder::post($uri, $handler);
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
        return RouteBuilder::put($uri, $handler);
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
        return RouteBuilder::patch($uri, $handler);
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
        return RouteBuilder::delete($uri, $handler);
    }

    /**
     * group
     *
     * @param  mixed $uri
     * @return void
     */
    public static function group(...$args)
    {
        return RouteBuilder::group(...$args);
    }

    /**
     * getRouteList
     *
     * @return array
     */
    public static function getRouteList()
    {
        return RouteBuilder::getRouteList();
    }

    /**
     * middleware
     *
     * @param  mixed $middleware
     * @return Static
     */
    public static function middleware($middleware)
    {
        return RouteBuilder::middleware($middleware);
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
        return RouteBuilder::match($slug, $regex);
    }

    /**
     * name
     *
     * @param  mixed $name
     * @return static
     */
    public static function name($name)
    {
        return RouteBuilder::name($name);
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
        return RouteBuilder::globalMiddleware($middleware, $clearPrevious);
    }

    /**
     * clearGlobalMiddleware
     *
     * @return void
     */
    public static function clearGlobalMiddleware()
    {
        return RouteBuilder::clearGlobalMiddleware();
    }

}
