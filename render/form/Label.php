<?php

namespace monolyth\render\form;
use monolyth\core\Element;

class Label
{
    private $element;
    private $txt;

    public function prepare(Element $element, $txt)
    {
        $this->element = $element;
        $this->txt = $txt;
    }

    public function __toString()
    {
        return sprintf(
            '<label for="%s">%s</label>'."\n",
            $this->element->getId(),
            $this->txt
        );
    }

    public function raw()
    {
        return $this->txt;
    }
}

