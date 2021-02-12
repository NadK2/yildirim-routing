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
     * files
     *
     * @var array
     */
    private $files = [];

    /**
     * __construct
     *
     * @param  mixed $attributes
     * @return void
     */
    public function __construct($attributes = [])
    {
        $this->parseHeaders();
        $this->getRequestData($attributes);
        $this->getUploadedFiles();
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
        return $this->headers()[strtolower($key)] ?? null;
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

    /**
     * parseHeaders
     *
     * @return void
     */
    private function parseHeaders()
    {
        foreach (server()->toArray() as $header => $value) {
            if (substr($header, 0, 5) == "HTTP_") {
                $this->headers[str_replace(" ", "-", (str_replace("_", " ", strtolower(substr($header, 5)))))] = $value;
            }
        }
    }

    /**
     * getRequestData
     *
     * @param  mixed $attributes
     * @return void
     */
    private function getRequestData($attributes)
    {
        foreach ($attributes ?: $_REQUEST as $key => $val) {
            $this->{$key} = $val;
        }
    }

    /**
     * hasFile
     *
     * @param  mixed $filename
     * @return mixed
     */
    public function hasFile($key)
    {
        return isset($this->files[$key]);
    }

    /**
     * files
     *
     * @return array
     */
    public function files()
    {
        return collect($this->files);
    }

    /**
     * file
     *
     * @param  mixed $filename
     * @return UploadedFile
     */
    public function file($key)
    {
        return $this->files[$key] ?? null;
    }

    /**
     * getUploadedFiles
     *
     * @return void
     */
    private function getUploadedFiles()
    {
        foreach ($_FILES as $name => $file) {
            $this->files[$name] = new UploadedFile($file, $name);
        }
    }
    
    /**
     * addFile
     *
     * @param  mixed $file
     * @param  mixed $name
     * @return static
     */
    public function addFile($file, $key)
    {
        $this->files[$key] = new UploadedFile($file, $key);
        return $this;
    }
}
