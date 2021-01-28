<?php

namespace Yildirim\Classes;

use ArrayAccess;
use Iterator;
use Yildirim\Interfaces\Arrayable;
use Yildirim\Interfaces\Jsonable;

class Collection implements ArrayAccess, Iterator, Jsonable, Arrayable
{

    /**
     *
     */
    private $position = 0;

    /**
     *
     */
    protected $items = [];

    /**
     * __construct
     *
     * @param  array $data
     * @return void
     */
    public function __construct($data = [])
    {
        $this->items = $data;
    }

    /**
     * all
     *
     * @return array
     */
    public function all()
    {
        return $this->toArray();
    }

    /**
     * count
     *
     * @return int
     */
    public function count()
    {
        return count($this->items);
    }

    /**
     * each
     *
     * @return void
     */
    public function each(callable $callback)
    {
        foreach ($this->items as $key => $item) {
            if ($callback($item, $key) === false) {
                break;
            }
        }

        return $this;
    }

    /**
     * except
     *
     * @return void
     */
    public function except($keys)
    {
        return call_user_func_array([$this, 'only'], array_diff($this->keys()->toArray(), (array) func_get_args()));
    }

    /**
     * filter
     *
     * @param  mixed $callback
     * @return Static
     */
    public function filter(callable $callback = null)
    {
        if ($callback) {
            return new static(array_filter($this->items, $callback));
        }

        return new static(array_filter($this->items));
    }

    /**
     * first
     *
     * @param  mixed $callback
     * @return void
     */
    public function first(callable $callback = null)
    {
        if ($callback) {
            return $this->filter($callback)->first();
        }

        return reset($this->items);
    }

    /**
     * has
     *
     * @param  mixed $value
     * @return void
     */
    public function has($value)
    {
        return in_array($value, $this->items);
    }

    /**
     * hasKey
     *
     * @param  mixed $value
     * @return void
     */
    public function hasKey($value)
    {
        return array_key_exists($value, $this->items);
    }

    /**
     * keys
     *
     * @return static
     */
    public function keys()
    {
        return new static(array_keys($this->items));
    }

    /**
     * map
     *
     * @param  mixed $callback
     * @return static
     */
    public function map(callable $callback)
    {
        $keys = array_keys($this->items);

        $items = array_map($callback, $this->items, $keys);

        return new static(array_combine($keys, $items));
    }

    /**
     * only
     *
     * @param  mixed $keys
     * @return Static
     */
    public function only($keys)
    {
        return new static(array_intersect_key($this->items, array_flip((array) func_get_args())));
    }

    /**
     * pluck
     *
     * @return array
     */
    public function pluck($column, $key = null)
    {
        return array_column($this->toArray(), $column, $key);
    }

    /**
     * values
     *
     * @return Static
     */
    public function values()
    {
        return new static(array_values($this->items));
    }

    /**
     * push
     *
     * @param  mixed $key|$value
     * @param  mixed $value
     * @return void
     */
    public function push($key, $value = null)
    {
        if ($value) {
            $this->items[$key] = $value;
        } else {
            $this->items[] = $key;
        }
    }

    /**
     * implode
     *
     * @return string
     */
    public function implode($glue = "")
    {
        return implode($glue, $this->items);
    }

    /**
     * rewind
     *
     * @return void
     */
    public function rewind()
    {
        $this->position = 0;
    }

    /**
     * current
     *
     * @return void
     */
    public function current()
    {
        return $this->array[$this->position];
    }

    /**
     * key
     *
     * @return void
     */
    public function key()
    {
        return $this->position;
    }

    /**
     * next
     *
     * @return void
     */
    public function next()
    {
        ++$this->position;
    }

    /**
     * valid
     *
     * @return void
     */
    public function valid()
    {
        return isset($this->array[$this->position]);
    }

    /**
     * offsetSet
     *
     * @param  mixed $offset
     * @param  mixed $value
     * @return void
     */
    public function offsetSet($offset, $value)
    {
        if (is_null($offset)) {
            $this->items[] = $value;
        } else {
            $this->items[$offset] = $value;
        }
    }

    /**
     * offsetExists
     *
     * @param  mixed $offset
     * @return void
     */
    public function offsetExists($offset)
    {
        return isset($this->items[$offset]);
    }

    /**
     * offsetUnset
     *
     * @param  mixed $offset
     * @return void
     */
    public function offsetUnset($offset)
    {
        unset($this->items[$offset]);
    }

    /**
     * offsetGet
     *
     * @param  mixed $offset
     * @return void
     */
    public function offsetGet($offset)
    {
        return isset($this->items[$offset]) ? $this->items[$offset] : null;
    }

    /**
     * toArray
     *
     * @return array
     */
    public function toArray($options = [])
    {

        // if ($options['response'] ?? null) {

        //     return $this->map(function ($item) {
        //         if ($item instanceof Arrayable) {
        //             return $item->toArray();
        //         }
        //         if ($item instanceof Jsonable) {
        //             return $item->toJson();
        //         }
        //         return $item;
        //     });

        // }

        return $this->items;
    }

    /**
     * toJson
     *
     * @return string
     */
    public function toJson($options = [])
    {
        return json_encode($this->toArray($options));
    }

    /**
     * __toString
     *
     * @return string
     */
    public function __toString()
    {
        return $this->toJson();
    }

}
