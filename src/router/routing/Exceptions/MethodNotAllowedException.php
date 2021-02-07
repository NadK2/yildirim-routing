<?php

namespace Yildirim\Routing\Exceptions;

use Exception;

class MethodNotAllowedException extends Exception
{
    public function __construct($message, $previous = null)
    {
        parent::__construct($message, 405, $previous);
    }
}
