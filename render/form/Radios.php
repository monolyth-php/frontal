<?php

namespace monolyth\render\form;
use monolyth\core\Element;

class Radios extends Element
{
    public $_Radio;
    protected $choices = [], $type = 'radios';

    public function prepare($name, array $choices, array $options = [])
    {
        parent::prepare($name, $options);
        if (!isset($options)) {
            $options = [];
        }
        $updated_id = false;
        foreach ($choices as $value => $text) {
            if (!$updated_id) {
                $this->id = $this->options['id'] = "{$this->id}-$value";
                $updated_id = true;
            }
            $options['value'] = $value;
            $o = new Radio;
            $o->setParent($this->parent);
            $o->prepare(
                $name,
                ['id' => self::nameToId($name)."-$value"] + $options
            );
            $o->setLabel($text);
            $this->choices[] = $o;
        }
    }

    public function makeInline()
    {
        foreach ($this->choices as &$choice) {
            $choice->makeInline();
        }
        return $this;
    }

    public function prependFormname($id)
    {
        foreach ($this->choices as $choice) {
            $choice->prependFormname($id);
        }
        parent::prependFormname($id);
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
            if ($choice->value == $value) {
                $choice->checked();
                $this->value =& $choice->value;
            } else {
                $choice->unchecked();
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

    /** This is a required field. */
    public function isRequired()
    {
        if (isset($this->choices[0])) {
            $this->choices[0]->isRequired();
        }
        return $this;
    }

    public function setClass($class)
    {
        foreach ($this->choices as $choice) {
            $choice->setClass($class);
        }
        return $this;
    }
}

