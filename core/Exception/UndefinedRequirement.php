<?php

/**
 * @package monolyth
 * @subpackage core
 */

namespace monolyth\core;

class UndefinedRequirement_Exception extends Exception
{
    public function __construct($name)
    {
        $this->message = "Undefined requirement: $name.";
    }
}

