<?php

namespace monolyth\core;

trait Singleton
{
    public static function instance()
    {
        static $instance;
        $class = get_called_class();
        if (!isset($instance) || get_class($instance) != $class) {
            $instance = new $class;
        }
        return $instance;
    }

    public function __clone()
    {
        trigger_error(
            'Cloning '.__CLASS__.' is not allowed.',
            E_USER_ERROR
        );
    }
    
    public function __wakeup()
    {
        trigger_error(
            'Unserializing '.__CLASS__.' is not allowed.',
            E_USER_ERROR
        );
    }
}

