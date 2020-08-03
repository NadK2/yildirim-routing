<?php

namespace Yildirim\Classes;

class Exception
{

    /**
     * __construct
     *
     * @param  mixed $type
     * @param  mixed $message
     * @param  mixed $code
     * @param  mixed $previous
     * @return void
     */
    public function __construct($type = 'Exception', $message = "", $code = 0, $previous = null)
    {

        if ($this->generateExceptionClass($type)) {

            throw new $type($message, $code, $previous);

        } else {

            throw new \Exception($message, $code, $previous);

        }

    }

    private function generateExceptionClass($type)
    {

        if ($type == 'Exception') {
            return false;
        }

        eval('

        class ' . $type . ' extends \Exception
        {
            public function __construct($message = "", $code = 0, $previous = null)
            {
                parent::__construct($message, $code, $previous);
            }

        }');

        return true;

    }

}
