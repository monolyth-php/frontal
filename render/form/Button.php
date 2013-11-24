<?php

namespace monolyth\render\form;

class Button
{
    private $text, $form, $value, $name = 'act_custom';
    protected static $type = 'button';

    public function __construct($form, $text, $name = null)
    {
        if (isset($name)) {
            $this->name = $name;
        }
        $this->text = $text;
        $this->value = $form->getId();
    }

    public function __toString()
    {
        return sprintf(
            '<button type="%s" name="%s" value="%s">%s</button>',
            static::$type,
            $this->name,
            $this->value,
            $this->text
        );
    }

    public function name()
    {
        return $this->name;
    }
}

