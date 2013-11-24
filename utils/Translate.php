<?php

namespace monolyth\utils;

final class Translate implements Array_Translatable
{
    /**
     * Private constructor, so the final class turns abstract and
     * cannot be instantiated.
     */
    private function __construct()
    {
    }

    public function translateKeys(array $array)
    {
        $new = [];
        foreach ($array as $key => $value) {
            if (strpos($key, '\\') === false) {
                $class = str_replace('_', '/', strtolower(get_called_class()));
                $key = "$class/$key";
            }
            $key = forward_static_call([__CLASS__, 'text'], $key);
            $new[$key] = $value;
        }
        return $new;
    }
    
    public function translateValues(array $array)
    {
        foreach ($array as &$row) {
            $fn = is_array($row) ? 'translateValues' : 'text';
            $row = forward_static_call([__CLASS__, $fn], $row);
        }
        return $array;
    }
}

