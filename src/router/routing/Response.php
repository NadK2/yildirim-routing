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

            $this->body = json_encode($this->body);
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

            header("$header: " . $value);
        }

        return $this;
    }

}
