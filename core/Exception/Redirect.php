<?php

/**
 * @package monolyth
 * @subpackage core
 */

namespace monolyth\core;

class Redirect_Exception extends Exception
{
    public function __construct($url)
    {
        $this->message = $url;
    }
}

