<?php

/**
 * This is the base monolyth\Exception.
 *
 * @package monolyth
 */
namespace monolyth;

/**
 * Abstract base monolyth Exception.
 */
abstract class Exception extends \Exception
{
    private $server;

    public function __construct($message = null, $code = 0,
        \Exception $previous = null
    )
    {
        parent::__construct($message, $code, $previous);
        $this->server = php_uname('n');
    }
}

