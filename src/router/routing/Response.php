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
class Response
{
    /**
     * contentType
     *
     * @var bool
     */
    private $contentType = false;

    /**
     *
     */
    public function __construct($response)
    {
        $this->body = $response;
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
        $this->setContentTypeHeader();

        echo $this->body;

        return;
    }

    /**
     * determineResponseType
     *
     * @return void
     */
    private function setContentTypeHeader()
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
    private function getContentType()
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
    private function isJson()
    {
        if ($this->body instanceof Jsonable) {

            $this->body = $this->body->toJson(['response' => true]);
            return true;

        } elseif ($this->body instanceof Arrayable) {

            $this->body = json_encode($this->body->toArray(['response' => true]));
            return true;

        } else if (is_array($this->body)) {

            $this->body = json_encode(array_map(function ($item) {
                return $this->getArrayValues($item);
            }, $this->body));

            return true;

        }

        return false;
    }

    /**
     *
     */
    private function isXml()
    {

        if (is_string($this->body)) {
            if (substr($this->body, 0, 6) === "<?xml ") {
                return true;
            }
        }

        if (is_object($this->body)) {
            if ($this->body instanceof SimpleXMLElement) {
                $this->body = $this->body->asXML();
                return true;
            }
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

            if ($header == 'Content-Type') {
                $this->contentType = true;
            }

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
        header("$header: " . $value);
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
     * @return void
     */
    private function getArrayValues($item)
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
     * __toString
     *
     * @return string
     */
    public function __toString()
    {
        return $this->body;
    }

    /**
     * file
     *
     * @param  string $filepath full path to file
     * @return void
     */
    public function file($filepath)
    {
        //get file contents.
        $this->body = file_get_contents($filepath);

        //get mime type.
        $mimeType = mime_content_type($filepath);

        //set headers.
        $this->setHeaders([
            'Content-Type' => $mimeType,
            'Content-Length' => filesize($filepath),
        ]);

        return $this;
    }

}
