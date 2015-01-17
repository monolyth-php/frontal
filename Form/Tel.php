<?php

namespace monolyth\render\form;

class Tel extends Text
{
    protected $type = 'tel';

    public function __set($name, $value)
    {
        if ($name != 'value') {
            return;
        }
        $value = preg_replace('/^\d/', '', $value);
        if (strlen($value)) {
            if ($value{0} != '0') {
                $value = "0$value";
            }
        }
        return parent::__set($name, $value);
    }
}

