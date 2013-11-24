<?php

namespace monolyth\render\form;
use monolyth\core\Element;

class Hidden extends Element
{
    protected $type = 'hidden', $renderOptions = ['name', 'type', 'value'];
}

