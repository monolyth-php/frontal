<?php

namespace monolyth\render\form;
use monolyth\core\Element;

class Radio extends Element
{
    protected $type = 'radio',
        $renderOptions = ['id', 'name', 'type', 'value'];

    public function prepare($name, array $options = [])
    {
        parent::prepare($name, $options);
        $this->unchecked();
        if (!isset($this->options['value'])) {
            $this->options['value'] = 1;
        }
        if (!isset($this->options['label'])) {
            $this->options['label'] = true;
        }
        $this->value =& $this->options['value'];
    }

    public function checked()
    {
        $this->options['checked'] = 'checked';
        $this->renderOptions[] = 'checked';
    }

    public function unchecked()
    {
        unset($this->options['checked']);
        foreach ($this->renderOptions as $key => $value) {
            if ($value == 'checked') {
                unset($this->renderOptions[$key]);
            }
        }
    }

    public function makeInline()
    {
        $this->type = 'radio/inline';
    }

    public function __set($name, $value)
    {
        if ($name != 'value') {
            return null;
        }
        if ($value == $this->options['value']) {
            $this->checked();
        } else {
            $this->unchecked();
        }
        return $value;
    }

    public function isChecked()
    {
        return isset($this->options['checked']);
    }

    /** This is a required field. */
    public function isRequired()
    {
        $this->options['required'] = true;
        $this->renderOptions[] = 'required';
        $error = self::ERROR_MISSING;
        return $this->addTest(function($value) use ($error) {
            return $this->isChecked() ? null : $error;
        });
    }

    public function showLabel()
    {
        return isset($this->options['label']) && $this->options['label'];
    }

    public function showInverted($set = null)
    {
        static $status = false;
        if (isset($set)) {
            $status = $set;
        }
        return $status;
    }
}

