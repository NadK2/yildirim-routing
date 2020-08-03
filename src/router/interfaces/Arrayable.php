<?php

namespace Yildirim\Interfaces;

interface Arrayable
{
    /**
     * Convert the object to its JSON representation.
     *
     * @param  int  $options
     * @return string
     */
    public function toArray();
}
