<?php

namespace monolyth\render\form;

class Checkbox extends Radio
{
    protected $type = 'checkbox';

    public function getValue()
    {
        return $this->options['value'];
    }
}

