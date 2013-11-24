<?php

/**
 * @package monolyth
 */

namespace monolyth;

class HTTPUndefined_Exception extends Exception
{
    public function __construct()
    {
        parent::__construct("An unsupported HTTP response was thrown.");
    }
}

