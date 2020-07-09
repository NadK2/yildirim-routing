<?php

namespace Yildirim\Routing;

class RequestRoute
{

    /**
     * attributes
     *
     * @var array
     */
    private $attributes = [];

    /**
     * __construct
     *
     * @param  mixed $route
     * @param  mixed $path
     * @return void
     */
    public function __construct($route, $path)
    {

        $this->setParameters($route->getSlugValues($path));

        $this->attributes = collect((array) $route)->except('parameters', 'callable', 'controller', 'function');

    }

    /**
     * route
     *
     * @var array
     */
    private $route = [];

    /**
     * setRouteParameters
     *
     * @param  mixed $data
     * @return void
     */
    public function setParameters(array $data)
    {
        foreach ($data as $key => $val) {
            $this->setParameter($key, $val);
        }
    }

    /**
     * setRouteParameter
     *
     * @param  mixed $key
     * @param  mixed $val
     * @return void
     */
    public function setParameter($key, $val)
    {
        $this->route[$key] = $val;
    }

    /**
     * getRouteParameters
     *
     * @return array
     */
    public function parameters()
    {
        return $this->route;
    }

    /**
     * parameter
     *
     * @param  mixed $key
     * @return mixed
     */
    public function parameter($key)
    {
        return $this->route[$key];
    }

    /**
     * get
     *
     * @param  mixed $key
     * @return mixed
     */
    public function get($key)
    {
        return $this->attributes[$key] ?? null;
    }

}
