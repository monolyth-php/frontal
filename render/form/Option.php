<?php

namespace monolyth\render\form;
use monolyth\core\Element;

class Option extends Element
{
    protected $type = 'option', $renderOptions = ['value'];

    public function selected()
    {
        $this->renderOptions[] = 'selected';
        $this->options['selected'] = 'selected';
    }

    public function unselected()
    {
        unset($this->options['selected']);
        foreach ($this->renderOptions as $key => $value) {
            if ($value == 'selected') {
                unset($this->renderOptions[$key]);
            }
        }
    }
}

