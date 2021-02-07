<?php

use Yildirim\Routing\Response;
use Yildirim\Routing\RouteBuilder;

$yildirim_container = null;

if (!function_exists('app')) {

    /**
     * app
     *
     * @param  mixed $abstract
     * @param  mixed $concrete
     * @return Yildirim\Classes\Container
     */
    function app($abstract = null, $parameters = [])
    {
        global $yildirim_container;

        if (!$yildirim_container) {
            $yildirim_container = new Yildirim\Classes\Container;
        }

        return ($abstract) ? $yildirim_container->get($abstract, $parameters) : $yildirim_container;
    }
}

if (!function_exists('collect')) {

    /**
     * collect
     *
     * @param  mixed $data
     * @return Yildirim\Classes\Collection
     */
    function collect($data = [])
    {
        if (!app()->has('collection')) {
            app()->set('collection', new Yildirim\Classes\Collection());
        }

        return app('collection', [$data]);
    }
}

if (!function_exists('server')) {

    /**
     * server
     *
     * @param  mixed $key
     * @param  mixed $defualt
     * @return Yildirim\Classes\Server
     */
    function server($key = null, $defualt = null)
    {

        if (!app()->has('server')) {
            app()->setInstance('server', new Yildirim\Classes\Server());
        }

        return $key ? (app('server')->{$key} ?: $defualt): app('server');
    }
}

if (!function_exists('session')) {
    /**
     * session
     *
     * @return mixed
     */
    function session($key = null, $defualt = null)
    {
        if (!app()->has('session')) {
            app()->setInstance('session', new Yildirim\Classes\Session());
        }

        return $key ? app('session')->get($key, $defualt) : app('session');
    }
}

if (!function_exists('request')) {
    /**
     * request
     *
     * @param  mixed $key
     * @param  mixed $defualt
     * @return Yildirim\Routing\Request
     */
    function request($key = null, $defualt = null)
    {
        if (!app()->has('request')) {
            app()->setInstance('request', app(Yildirim\Routing\Request::class));
        }

        return $key ? (app('request')->{$key} ?: $defualt): app('request');
    }
}

if (!function_exists('csrf')) {
    /**
     * csrf
     *
     * @return string
     */
    function csrf()
    {
        return session()->csrf();
    }
}

if (!function_exists('route')) {
    /**
     * route
     *
     * @return string
     */
    function route($name, $parameters = [])
    {
        return RouteBuilder::reverseRouteLookup($name, $parameters);
    }
}

if (!function_exists('response')) {
    /**
     * response
     *
     * @param  mixed $response
     * @param  mixed $headers
     * @return mixed
     */
    function response(string $response = '')
    {
        if (!app()->has('response')) {
            app()->set('response', Response::class);
        }

        return app('response', [$response]);
    }
}
