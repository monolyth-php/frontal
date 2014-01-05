<?php

namespace monolyth\core;

trait Singleton
{
    public static function instance()
    {
        static $instance;
        if (!isset($instance)) {
            $class = __CLASS__;
            $instance = new $class;
        }
        return $instance;
    }
}

