<?php

namespace monolyth\render\form;
use monolyth\core\Element;

class Checkboxes extends Element
{
    public $_Checkbox, $_Hidden;
    protected $choices = [], $type = 'checkboxes';

    public function prepare($name, array $choices, array $options = [])
    {
        parent::prepare($name, $options);
        if (!isset($options)) {
            $options = [];
        }
        $updated_id = false;
        $o = clone $this->_Hidden;
        $o->setParent($this->parent);
        $o->prepare("{$name}[0]");
        $this->choices[] = $o;
        foreach ($choices as $value => $text) {
            if (!strlen($value)) {
                continue;
            }
            $options['value'] = $value;
            if (!$updated_id) {
                $this->id = "{$this->id}-$value";
                $updated_id = true;
            }
            $cname = "{$name}[$value]";
            $o = clone $this->_Checkbox;
            $o->setParent($this->parent);
            $o->prepare($cname, $options);
            $o->setLabel($text);
            $this->choices[] = $o;
        }
    }

    public function prependFormname($id)
    {
        foreach ($this->choices as $choice) {
            $choice->prependFormname($id);
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
        foreach ($this->choices as $choice) {
            if (method_exists($choice, 'unchecked')) {
                $choice->unchecked();
            }
        }
        if (!is_array($value)) {
            $value = [$value];
        }
        foreach ($value as $data) {
            if (!$data) {
                continue;
            }
            foreach ($this->choices as $choice) {
                if (method_exists($choice, 'getValue')
                    && $choice->getValue() == $data
                ) {
                    $choice->checked();
                }
            }
        }
        $this->value = $value;
        return $value;
    }

    public function showInverted($set = null)
    {
        static $status = false;
        if (isset($set)) {
            $status = $set;
        }
        return $status;
    }

    public function setClass($class)
    {
        foreach ($this->choices as $choice) {
            $choice->setClass($class);
        }
        return $this;
    }
}

