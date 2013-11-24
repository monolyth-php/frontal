<?php

namespace monolyth\render\form;

class Bitflags extends Checkboxes
{
    public function __set($name, $value)
    {
        if ($name != 'value') {
            return null;
        }
        if (!is_array($value)) {
            $value = self::extract($value);
        }
        return parent::__set($name, $value);
    }

    public function __get($name)
    {
        if ($name !== 'value') {
            return null;
        }
        $value = parent::__get($name);
        $new = 0;
        if (!is_array($value)) {
            $value = (int)$value;
            $value = self::extract($value);
        }
        foreach ($value as $bit) {
            $new |= $bit;
        }
        if (!$new && $this->nullAllowed()) {
            return null;
        }
        return $new;
    }    

    public static function extract($value)
    {
        // Split into in an array with all the relevant flags.
        $tmp = [];
        for ($i = 1; $i <= $value; $i *= 2) {
            if ($value & $i) {
                $tmp[$i] = $i;
            }
        }
        return $tmp;
    }
}

