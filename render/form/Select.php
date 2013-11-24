<?php

namespace monolyth\render\form;
use monolyth\core\Element;

class Select extends Element
{
    public $_Option;
    protected $type = 'select', $renderOptions = ['id', 'name'], $choices = [];

    public function prepare($name, array $choices, array $options = [])
    {
        parent::prepare($name, $options);
        if (!isset($choices)) {
            $choices = [];
        }
        foreach ($choices as $value => $text) {
            if (is_null($text)) {
                $text = $value;
            }
            $o = clone $this->_Option;
            $o->prepare($name);
            $o->setLabel($text);
            $o->value = $value;
            $this->choices[] = $o;
        }
    }

    public function getChoices()
    {
        return $this->choices;
    }

    public function __set($name, $value)
    {
        if ($name != 'value') {
            return null;
        }
        if (isset($this->options['required']) && $this->options['required']) {
            if (!strlen(trim($value))) {
                $value = null;
            }
        }
        $this->value = $value;
        foreach ($this->choices as $choice) {
            if ("{$choice->value}" == "$value") {
                $choice->selected();
            } else {
                $choice->unselected();
            }
        }
        return $value;
    }

    public function notNull()
    {
        if (isset($this->choices[0]) && $this->choices[0]->value === '') {
            unset($this->choices[0]);
        }
    }
}

