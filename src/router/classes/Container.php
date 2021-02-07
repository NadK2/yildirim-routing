<?php

namespace Yildirim\Classes;

use Yildirim\Classes\Exceptions\ContainerException;
use Yildirim\Routing\Router;

/**
 * Class Container
 */
class Container
{
    /**
     * @var array
     */
    protected $instances = [];

    /**
     * set
     *
     * @param      $abstract
     * @param null $concrete
     */
    public function set($abstract, $concrete = null)
    {
        if ($concrete === null) {
            $concrete = $abstract;
        }
        $this->instances[$abstract] = $concrete;

    }

    /**
     * setInstance
     *
     * @param  mixed $abstract
     * @param  mixed $concrete
     * @return void
     */
    public function setInstance($abstract, $instance)
    {
        $this->instances[$abstract] = function () use ($instance) {return $instance;};

        //if the object is aliased the namespace path is also registered.
        if (is_object($instance)) {
            $this->instances[get_class($instance)] = function () use ($instance) {return $instance;};
        }

    }

    /**
     * get
     *
     * @param       $abstract
     * @param array $parameters
     *
     * @return mixed|null|object
     * @throws Exception
     */
    public function get($abstract, $parameters = [])
    {
        // if we don't have it, just register it
        if (!isset($this->instances[$abstract])) {
            $this->set($abstract);
        }

        return $this->resolve($this->instances[$abstract], $parameters);
    }

    /**
     * has
     *
     * @param  mixed $abstract
     * @return bool
     */
    public function has($abstract)
    {
        return isset($this->instances[$abstract]);
    }

    /**
     * resolve single
     *
     * @param $concrete
     * @param $parameters
     *
     * @return mixed|object
     * @throws Exception
     */
    public function resolve($concrete, $parameters)
    {

        if ($concrete instanceof \Closure) {
            return $concrete($this, $parameters);
        }

        $reflector = new \ReflectionClass($concrete);
        // check if class is instantiable
        if (!$reflector->isInstantiable()) {
            throw new \Exception("Class {$concrete} is not instantiable");
        }

        // get class constructor
        $constructor = $reflector->getConstructor();
        if (is_null($constructor)) {
            // get new instance from class
            return $reflector->newInstance();
        }

        // get constructor params
        $requiredParams = $constructor->getParameters();
        $dependencies = $this->getDependencies($parameters, $requiredParams);

        // get new instance with dependencies resolved
        return $reflector->newInstanceArgs($dependencies);
    }

    /**
     * getDependencies
     *
     * @param  mixed $parameters
     * @param  mixed $requiredParams
     * @return array
     *
     * @throws Exception
     */
    public function getDependencies($parameters, $requiredParams)
    {

        $position = 0;
        $dependencies = [];
        foreach ($requiredParams as $parameter) {

            // get the type hinted class
            $dependency = $parameter->getClass();
            if ($dependency === null) {

                if (isset($parameters[$position])) {
                    //check if a parameters has been passed.
                    $dependencies[] = $parameters[$position++];

                } elseif ($parameter->isDefaultValueAvailable()) {
                    //if no parameter has been passed check for default value.
                    $dependencies[] = $parameter->getDefaultValue();

                } else {
                    //if parameter cannot be resolved
                    throw new ContainerException("Can not resolve class dependency {$parameter->name}");
                }

            } else {
                // get dependency resolved
                $dependencies[] = $this->get($dependency->name);
            }

        }

        return $dependencies;
    }

    /**
     * unset
     *
     * @param  mixed $abstract
     * @return void
     */
    public function remove($abstract)
    {
        unset($this->instances[$abstract]);
    }

    /**
     * resolveClosure
     *
     * @param  mixed $closure
     * @param  mixed $parameters
     * @return mixed
     */
    public function resolveFunction($closure, $parameters)
    {
        if (!is_callable($closure)) {
            throw new ContainerException('Handler is not valid closure.');
        }

        $method = new \ReflectionFunction($closure);

        $parameters = $this->getDependencies($parameters, $method->getParameters());

        return $method->invokeArgs($parameters);
    }

    /**
     * resolveMethod
     *
     * @param  mixed $closure
     * @param  mixed $parameters
     * @return mixed
     */
    public function resolveMethod($class, $method, $parameters)
    {
        $method = new \ReflectionMethod($class, $method);

        $parameters = $this->getDependencies($parameters, $method->getParameters());

        return $method->invokeArgs($class, $parameters);

    }

    /**
     * start
     *
     * @return Yildirim\Routing\Response
     */
    public function start()
    {
        return Router::processRequest();
    }

}
