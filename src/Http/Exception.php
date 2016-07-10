<?php

namespace Monolyth\Frontal\Http;

class Exception extends \Exception
{
    public function __construct($code = 0, \Exception $previous = null)
    {
        $message = '';
        if (isset($previous)) {
            $message = $previous->getMessage();
        }
        parent::__construct($message, $code, $previous);
    }
}

