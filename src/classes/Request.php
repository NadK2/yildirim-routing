<?php

namespace Yildirim\Routing;

use Yildirim\Interfaces\Arrayable;
use Yildirim\Interfaces\Jsonable;
use Yildirim\Traits\HasAttributes;

class Request implements Jsonable, Arrayable
{

    use HasAttributes;

    /**
     * attributes
     *
     * @var array
     */
    protected $attributes = [];

    /**
     * route
     *
     * @var Yildirim\Routing\RequestRoute
     */
    private $route;

    /**
     * __construct
     *
     * @param  mixed $attributes
     * @return void
     */
    public function __construct($attributes = [])
    {
        foreach ($attributes ?: $_REQUEST as $key => $val) {
            $this->{$key} = $val;
        }

    }

    /**
     * server
     *
     * @param  mixed $key
     * @param  mixed $default
     * @return void
     */
    public function server($key = null, $default = null)
    {
        return server($key, $default);
    }

    /**
     * uri
     *
     * @return void
     */
    public function uri()
    {
        return urldecode(
            parse_url(server('REQUEST_URI'), PHP_URL_PATH)
        );
    }

    /**
     * method
     *
     * @return string
     */
    public function method()
    {
        return server('REQUEST_METHOD');
    }

    /**
     * route
     *
     * @param  mixed $key
     * @return Yildirim\Routing\RouteRequest
     */
    public function route($key = null)
    {
        if (!$this->route) {
            $this->route = app(RequestRoute::class);
        }
        return $key ? $this->route->parameter($key) : $this->route;
    }

    /**
     * ip
     *
     * @return void
     */
    public function ip()
    {
        return server()->ip();
    }

}
