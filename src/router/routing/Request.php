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
     * headers
     *
     * @var array
     */
    private $headers = [];

    /**
     * user
     *
     * @var undefined
     */
    private $user = null;

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
     * isAjax
     *
     * @return void
     */
    public function isAjax()
    {
        return 'xmlhttprequest' == strtolower(server('HTTP_X_REQUESTED_WITH'));
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
    public function userIP()
    {
        return server()->ip();
    }

    /**
     * has
     *
     * @param  mixed $key
     * @return bool
     */
    public function has($key)
    {
        return isset($this->attributes[$key]);
    }

    /**
     * headers
     *
     * @return array
     */
    public function headers()
    {
        if (!$this->headers) {
            foreach (server()->toArray() as $header => $value) {
                if (substr($header, 0, 5) == "HTTP_") {
                    $this->headers[str_replace(" ", "-", ucwords(str_replace("_", " ", strtolower(substr($header, 5)))))] = $value;
                }
            }
        }

        return $this->headers;
    }

    /**
     * header
     *
     * @param  mixed $key
     * @return mixed
     */
    public function header($key)
    {
        return $this->headers()[$key] ?? null;
    }

    /**
     * setUser
     *
     * @return void
     */
    public function setUser($user)
    {
        $this->user = $user;
    }

    /**
     * user
     *
     * @return mixed
     */
    public function user()
    {
        return $this->user;
    }
}
