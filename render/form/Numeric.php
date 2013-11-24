<?php

namespace monolyth\render\form;

class Numeric extends Text
{
    public function __set($name, $value)
    {
        if ($name != 'value') {
            return;
        }
        $value = preg_replace('/[^0-9.,]/', '', $value);
        // Guesstimate if this uses a . or , as decimal separator:
        if (strrpos($value, '.') || strrpos($value, ',')) {
            if (strrpos($value, '.') > strrpos($value, ',')) {
                $value = str_replace(',', '', $value);
            } else {
                $value = str_replace('.', '', $value);
                $value = str_replace(',', '.', $value);
            }
        } else {
            $value = str_replace(['.', ','], '', $value);
        }
        return parent::__set($name, $value);
    }
}

