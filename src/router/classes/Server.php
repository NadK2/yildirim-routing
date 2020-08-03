<?php

namespace Yildirim\Classes;

use Yildirim\Traits\HasAttributes;

class Server
{

    use HasAttributes;

    /**
     *
     */
    protected $attributes = [];

    /**
     * __construct
     *
     * @param  mixed $attributes
     * @return void
     */
    public function __construct($attributes = [])
    {
        foreach ($attributes ?: $_SERVER as $key => $val) {
            $this->{$key} = $val;
        }

    }

    /**
     * document_root
     *
     * @return string
     */
    public function document_root()
    {
        return str_replace("/public", "", $this->attributes['DOCUMENT_ROOT']);
    }

    /**
     * protocol
     *
     * @return string
     */
    public function protocol()
    {
        return $this->attributes['SERVER_PROTOCOL'];
    }

    /**
     * user_ip
     *
     * @return string
     */
    public function ip()
    {
        return $this->attributes['REMOTE_ADDR'];
    }
}
