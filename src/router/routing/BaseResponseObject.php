<?php

namespace Yildirim\Routing;

use SimpleXMLElement;
use Yildirim\Interfaces\Arrayable;
use Yildirim\Interfaces\Jsonable;

/**
 * Basic Response Class
 *
 *
 */
class BaseResponseObject
{
    /**
     * contentType
     *
     * @var bool
     */
    protected $contentType = false;

    /**
     * headers
     *
     * @var array
     */
    protected $headers = [];

    /**
     * body
     *
     * @var string
     */
    protected $body = '';

    /**
     *
     */
    public function __construct($response = '')
    {
        $this->setBody($response);
    }

    /**
     * getBody
     *
     * @return void
     */
    public function getBody()
    {
        return $this->body;
    }

    /**
     * setBody
     *
     * @param  mixed $body
     * @return void
     */
    public function setBody($body)
    {
        $this->body = $body;
        return $this;
    }

    /**
     *
     */
    public function send()
    {
        //apply headers.
        $this->sendHeaders();

        //if content type header not set, the set.
        $this->setContentTypeHeader();

        echo $this->body;

        exit;
    }

    /**
     * determineResponseType
     *
     * @return void
     */
    protected function setContentTypeHeader()
    {
        if ($this->contentType) {
            return;
        }

        if ($type = $this->getContentType()) {

            $this->contentType = true;

            return header('Content-Type: ' . $type);

        }

        return;

    }

    /**
     * getContentType
     *
     * @return void
     */
    protected function getContentType()
    {
        if ($this->isJson()) {
            return 'application/json';
        }

        if ($this->isXml()) {
            return 'application/xml';
        }

        return false;
    }

    /**
     *
     */
    protected function isJson()
    {
        if ($this->body instanceof Jsonable) {

            $this->body = $this->body->toJson(['response' => true]);
            return true;

        } elseif ($this->body instanceof Arrayable) {

            $this->body = json_encode($this->body->toArray(['response' => true]));
            return true;

        } elseif (is_array($this->body)) {

            $this->body = json_encode(array_map(function ($item) {
                return $this->getArrayValues($item);
            }, $this->body));

            return true;

        } elseif (is_string($this->body)) {
            json_decode($this->body);
            if ((json_last_error() == JSON_ERROR_NONE)) {
                return true;
            }
        }

        return false;
    }

    /**
     * isXml
     *
     * @return bool
     */
    protected function isXml()
    {
        if (is_string($this->body)) {
            if (substr($this->body, 0, 6) === "<?xml ") {
                return true;
            }
        }

        if ($this->body instanceof SimpleXMLElement) {
            $this->body = $this->body->asXML();
            return true;
        }

        return false;
    }

    /**
     * withHeaders
     *
     * @param  mixed $headers
     * @return static
     */
    public function setHeaders(array $headers)
    {
        foreach ($headers as $header => $value) {
            $this->setHeader($header, $value);
        }

        return $this;
    }

    /**
     * setHeader
     *
     * @param  mixed $header
     * @param  mixed $value
     * @return static
     */
    public function setHeader($header, $value)
    {
        $this->headers[$header] = $value;
        return $this;
    }

    /**
     * setCode
     *
     * @param  mixed $code
     * @return static
     */
    public function setCode($code)
    {
        http_response_code($code);
        return $this;
    }

    /**
     * getArrayValues
     *
     * @return mixed
     */
    protected function getArrayValues($item)
    {

        if ($item instanceof Arrayable) {
            return $item->toArray();
        }

        if ($item instanceof Jsonable) {
            return $item->toJson();
        }

        if (is_array($item)) {
            foreach ($item as &$i) {
                $i = $this->getArrayValues($i);
            }
        }

        return $item;
    }

    /**
     * sendHeaders
     *
     * @return void
     */
    private function sendHeaders()
    {
        foreach ($this->headers as $header => $value) {

            if (strtolower($header) == 'content-type') {
                $this->contentType = true;
            }

            header("$header: " . $value);
        }
    }

    /**
     * __toString
     *
     * @return string
     */
    public function __toString()
    {
        return $this->body;
    }
}
