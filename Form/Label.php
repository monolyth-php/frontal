<?php

namespace monolyth\Form;

class Label
{
    private $element;
    private $txt;
    private $options = [];

    public function prepare(Element $element, $txt)
    {
        $this->element = $element;
        $this->txt = $txt;
    }

    public function __toString()
    {
        $options = [];
        foreach ($this->options as $key => $value) {
            $options[] = sprintf('%s="%s"', $key, htmlentities($value));
        }
        return sprintf(
            '<label for="%s"%s>%s</label>'."\n",
            $this->element->getId(),
            implode(' ', $options),
            $this->txt
        );
    }

    public function raw()
    {
        return $this->txt;
    }

    public function setOption($name, $value)
    {
        $this->options[$name] = $value;
        return $this;
    }
    
    public function getOption($name)
    {
        return isset($this->options[$name]) ?
            $this->options[$name] :
            null;
    }
}

