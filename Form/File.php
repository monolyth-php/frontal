<?php

namespace monolyth\render\form;
use monolyth\core\Element;

class File extends Element
{
    protected $type = 'file';

    public function __get($name)
    {
        if ($name != 'value') {
            return null;
        }
        return $this->value && isset($this->value['name']) ?
            $this->value['name'] :
            null;
    }

    public function __set($name, $value)
    {
        return null;
    }

    public function renderOptions()
    {
        unset($this->options['value']);
        foreach ($this->renderOptions as $i => $value) {
            if ($value == 'value') {
                unset($this->renderOptions[$i]);
            }
        }
        return parent::renderOptions();
    }

    /** This is a required field. */
    public function isRequired()
    {
        $this->options['required'] = true;
        $this->renderOptions[] = 'required';
        $error = self::ERROR_MISSING;
        return $this->addTest(function($value) use ($error) {
            return strlen(trim($value)) || isset($_FILES[$this->name]) ?
                null :
                $error;
        });
    }
}

