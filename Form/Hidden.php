<?php

namespace monolyth\Form;

class Hidden extends Element
{
    protected $type = 'hidden', $renderOptions = ['name', 'type', 'value'];
}

