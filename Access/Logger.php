<?php

namespace monolyth;
use monolyth\adapter\Logger;

trait Logger_Access
{
    public static function logger()
    {
        static $logger;
        if (!isset($logger)) {
            $logger = new Logger;
        }
        return $logger;
    }
}

