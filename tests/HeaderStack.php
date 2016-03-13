<?php

namespace Monolyth\Test;

/**
 * Store output artifacts
 */
class HeaderStack
{
    /**
     * @var array
     */
    private static $data = [];

    /**
     * Reset state
     */
    public static function reset()
    {
        self::$data = [];
    }

    /**
     * Push a header on the stack
     *
     * @param string $header
     */
    public static function push($header)
    {
        self::$data[] = $header;
    }

    /**
     * Return the current header stack
     *
     * @return array
     */
    public static function stack()
    {
        return self::$data;
    }
}

namespace Zend\Diactoros\Response;

use Monolyth\Test\HeaderStack;

/**
 * Have headers been sent?
 *
 * @return false
 */
function headers_sent()
{
    return false;
}

/**
 * Emit a header, without creating actual output artifacts
 *
 * @param string $value
 */
function header($value)
{
    HeaderStack::push($value);
}

