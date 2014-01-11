<?php

namespace monolyth;

trait Logger_Access
{
    public static function logger()
    {
        static $logger;
        if (!isset($logger)) {
            $logger = Logger::instance();
        }
        return $logger;
    }
}

