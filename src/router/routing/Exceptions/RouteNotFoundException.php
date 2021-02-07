<?php

namespace Yildirim\Routing\Exceptions;

use Exception;

class RouteNotFoundException extends Exception
{

    public function __construct($message, $previous = null)
    {
        parent::__construct($message, 404, $previous);
    }

}
