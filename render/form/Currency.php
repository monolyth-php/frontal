<?php

namespace monolyth\render\form;

class Currency extends Numeric
{
    public function __set($name, $value)
    {
        if ($name != 'value') {
            return;
        }
        $value = preg_replace('/[^0-9.,]/', '', $value);
        // "123,-" or "123.-" is translated to "123.00".
        $value = preg_replace('/([,.])--?$/', "\\1".'00', $value);
        return parent::__set($name, $value);
    }
}

