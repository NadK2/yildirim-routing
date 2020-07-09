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
     *
     */
    public function __construct($response)
    {
        $this->response = $response;
    }

    /**
     *
     */
    public function send()
    {

        $this->setContentTypeHeader();

        echo $this->response;

        return;
    }

    /**
     * determineResponseType
     *
     * @return void
     */
    private function setContentTypeHeader()
    {

        if ($type = $this->getContentType()) {

            return header('Content-Type: ' . $type);

        }

        return;

    }

    /**
     * isTypeJson
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

        if ($this->response instanceof Jsonable) {

            $this->response = $this->response->toJson();
            return true;

        } elseif ($this->response instanceof Arrayable) {

            $this->response = json_encode($this->response->toArray());
            return true;

        } else if (is_array($this->response)) {

            $this->response = json_encode($this->response);
            return true;

        }

        return false;
    }

    /**
     *
     */
    private function isXml()
    {

        if (is_string($this->response)) {
            if (substr($this->response, 0, 6) === "<?xml ") {
                return true;
            }
        }

        if (is_object($this->response)) {
            if ($this->response instanceof SimpleXMLElement) {
                $this->response = $this->response->asXML();
                return true;
            }
        }

        return false;
    }

}
