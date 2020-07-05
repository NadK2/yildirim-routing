<?php

namespace Yildirim\Routing;

/**
 *
 */
class Parameter
{

    /**
     * value
     *
     * @var mixed
     */
    public $value;

    /**
     * position
     *
     * @var mixed
     */
    public $position;

    /**
     * __construct
     *
     * @param  mixed $parameter
     * @return void
     */
    public function __construct($parameter, $position)
    {
        $this->value = $parameter;
        $this->position = $position;
    }

    /**
     * isSlug
     *
     * @return void
     */
    public function isSlug()
    {
        return (strpos($this->value, "{", 0) !== false && strpos($this->value, "}", strlen($this->value) - 1) !== false);
    }

    /**
     * isOptional
     *
     * @return bool
     */
    public function isOptional()
    {
        return (strpos($this->value, "?", strlen($this->value) - 2) !== false);
    }

    /**
     * slugIdentifier
     *
     * @return string
     */
    public function slugIdentifier()
    {
        if ($this->isSlug()) {
            return str_replace("?", "", str_replace("{", "", str_replace("}", "", $this->value)));
        }

        return null;
    }

    /**
     * __string
     *
     * @return string
     */
    public function __string()
    {
        return $this->value;
    }
}
