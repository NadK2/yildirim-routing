<?php

namespace Yildirim\Routing;

use SimpleXMLElement;
use Yildirim\Routing\Exceptions\ResponseException;

class Response extends BaseResponseObject
{

    /**
     * file
     *
     * @param  string $filepath full path to file
     * @return FileResponse
     */
    public function file($filepath, $filename = null)
    {
        return new FileResponse($filepath, $filename);
    }

    /**
     * json
     *
     * @param  array|string $body
     * @return static
     */
    public function json($body = null)
    {
        if ($body !== null) {
            $this->body = $body;
        }

        if (!$this->isJson()) {
            throw new ResponseException('Response is not valid json data.');
        }

        return $this->setHeader('Content-Type', 'application/json');
    }

    /**
     * xml
     *
     * @param  SimpleXMLElement|string $body
     * @return void
     */
    public function xml($body = null)
    {
        if ($body !== null) {
            $this->body = $body;
        }

        if (!$this->isXml()) {
            throw new ResponseException('Response is not valid xml data.');
        }

        return $this->setHeader('Content-Type', 'application/xml');
    }

    /**
     * text
     *
     * @param  string $body
     * @return void
     */
    public function plain(string $body = null)
    {
        if ($body !== null) {
            $this->body = $body;
        }

        return $this->setHeader('Content-Type', 'text/plain');
    }

    /**
     * plain
     *
     * @param  mixed $body
     * @return void
     */
    public function html(string $body = null)
    {
        if ($body !== null) {
            $this->body = $body;
        }

        return $this->setHeader('Content-Type', 'text/html');
    }

    /**
     * redirect
     *
     * @param  mixed $location
     * @return void
     */
    public function redirect($location)
    {
        header('location: ' . $location);
    }

}
