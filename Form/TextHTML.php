<?php

namespace monolyth\render\form;
use monolyth\core\Element;

class TextHTML extends Textarea
{
    public function prepare($name, array $options = [])
    {
        parent::prepare($name, $options);
        $this->renderOptions[] = 'class';
        $basename = preg_replace("@(\[.*?\])*@", '', $name);
        $this->setClass("html $basename");
    }

    public function setEditor($options)
    {
        $this->renderOptions[] = 'data-ckeditor';
        $this->options['data-ckeditor'] = base64_encode(json_encode($options));
    }
}

