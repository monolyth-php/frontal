<?php

/**
 * @package monolyth
 * @subpackage core
 */

namespace monolyth\core;

class LanguageNotFound_Exception extends Exception
{
    public function __construct($language)
    {
        $this->message = "The supplied language $language is not valid for this
            site.";
    }
}

