<?php

/**
 * @package monolyth
 * @subpackage utils
 */

namespace monolyth\utils;

class RouteNotFound_Exception extends Exception
{
    public function __construct($link)
    {
        $this->message = "I couldn't find a matching route for the link $link.";
    }
}

