<?php

namespace Yildirim\Routing\Exceptions;

use Exception;

class CsrfException extends Exception
{
    /**
     * __construct
     *
     * @param  mixed $message
     * @param  mixed $previous
     * @return void
     */
    public function __construct($message = 'csrf token mismatch', $previous = null)
    {
        parent::__construct($message, 403, $previous);
    }
}
