<?php

namespace Monolyth\Http;

class Exception extends \Exception
{
    public function __construct($code = 0, \Exception $previous = null)
    {
        parent::__construct('', $code, $previous);
    }
}

