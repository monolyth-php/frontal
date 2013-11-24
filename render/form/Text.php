<?php

namespace monolyth\render\form;
use monolyth\core\Element;

class Text extends Element
{
    protected $type = 'text',
        $renderOptions = ['id', 'name', 'type', 'value', 'size', 'maxlength'];

    public function size($size)
    {
        $this->options['size'] = $size;
        return $this;
    }
}

