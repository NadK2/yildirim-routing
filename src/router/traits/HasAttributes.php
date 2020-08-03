<?php

namespace Yildirim\Traits;

trait HasAttributes
{

    /**
     * original
     *
     * @var array
     */
    private $original = [];

    /**
     * __get
     *
     * @param  mixed $key
     * @return void
     */
    public function __get($key)
    {
        return $this->attributes[$key] ?? null;
    }

    /**
     * __set
     *
     * @param  mixed $key
     * @param  mixed $val
     * @return void
     */
    public function __set($key, $val)
    {
        $this->attributes[$key] = $val;

        if (debug_backtrace()[1]['function'] == "__construct") {
            $this->original[$key] = $val;
        }
    }

    /**
     * toArray
     *
     * @return void
     */
    public function toArray()
    {
        return $this->attributes;
    }

    /**
     * toJson
     *
     * @return void
     */
    public function toJson()
    {
        return json_encode($this->toArray());
    }

    /**
     * __toString
     *
     * @return void
     */
    public function __toString()
    {
        return $this->toJson();
    }

    /**
     * refresh  replaces attribute values with original value.
     *
     * @return void
     */
    public function refresh()
    {
        $this->attributes = $this->original;
        return;
    }

    /**
     * original
     *
     * @return void
     */
    public function original($key = null, $default = null)
    {
        return !$key ? $this->original : ($this->original[$key] ?? $default);
    }
}
