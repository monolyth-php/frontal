<?php

namespace Monolyth\Frontal;

abstract class Utilities
{
    /**
     * Required to make PHP handle UTF8 in a sane way. To use, simply include
     * somewhere early in your front controller (typically index.php).
     *
     * @return void
     */
    public static function utf8()
    {
        if (function_exists('mb_internal_encoding')) {
            mb_internal_encoding('UTF-8');
        }
        if (function_exists('mb_detect_order')) {
            mb_detect_order([
                'CP1251', 'CP1252', 'ISO-8859-1', 'UTF-8',
            ]);
        }
    }

    /**
     * Correct REMOTE_ADDR if we're behind a proxy.
     * This code is by no means extensive; there's prolly 1.000 other
     * cases you'll want to handle. Roll your own in that case.
     *
     * @return void
     */
    public static function proxy()
    {
        if (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $_SERVER['REMOTE_ADDR'] = trim(array_shift(explode(
                ',',
                $_SERVER['HTTP_X_FORWARDED_FOR']
            )));
        }
    }
}

