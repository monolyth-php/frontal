<?php

namespace monolyth;
use ErrorException;
use StdClass;

abstract class Config
{
    private static $configs = [];
    public static $container;

    public static function get($name, array $args = [])
    {
        if (strpos($name, '\\') !== false) {
            $name = substr($name, 0, strpos($name, '\\'));
        }
        static $done = [];
        $parsedName = $name;
        $parts = explode('_', $name);
        $base = array_shift($parts);
        if ($base != $name) {
            $parsedName = str_replace("{$base}_", '', $name);
        }
        if (isset($done[$name])) {
            return self::$configs[$parsedName];
        }
        $done[$name] = true;

        // Try to get custom config file.
        try {
            $config = self::load("config/$name.php", $args);
        } catch (ErrorException $e) {
            $config = [];
        }

        // Try to get default config file.
        try {
            $config += self::load("$base/config/$parsedName.php", $args);
        } catch (ErrorException $e) {
        }

        // Make it a StdClass with members.
        if (!isset(self::$configs[$parsedName])) {
            self::$configs[$parsedName] = new StdClass;
        }
        foreach ($config as $key => $value) {
            self::$configs[$parsedName]->$key = $value;
        }
        return self::$configs[$parsedName];
    }

    private static function load($__name, array $__args)
    {
        $data = call_user_func(function() use($__name, $__args) {
            extract($__args);
            include $__name;
            return get_defined_vars();
        });
        foreach (['__name', '__args'] as $test) {
            if ($data[$test] == $$test) {
                unset($data[$test]);
            }
        }
        return $data;
    }
}

