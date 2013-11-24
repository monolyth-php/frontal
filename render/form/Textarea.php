<?php

namespace monolyth\render\form;
use monolyth\core\Element;

class Textarea extends Element
{
    protected $renderOptions = ['id', 'name', 'type'];
    protected $type = 'textarea';
}

