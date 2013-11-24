<?php

/**
 * @package monolyth
 * @subpackage core
 */

namespace monolyth\core;

class HelperNotFound_Exception extends Exception
{
    public function __construct($name)
    {
        $this->message = $name;
    }
}

