<?php

namespace monolyth\render\form;

class Media extends File
{
    protected $type = 'media', $source = null, $width, $height, $index;

    public function __get($name)
    {
        if ($name != 'value') {
            return null;
        }
        if ($this->nullAllowed && !strlen(trim($this->value))) {
            return null;
        }
        return $this->value;
    }
    
    public function __set($name, $value)
    {
        if ($name != 'value') {
            return null;
        }
        if (!(isset($this->options['required'])
            && $this->options['required']
        )) {
            if (!((is_array($value) && $value) || strlen(trim($value)))) {
                $value = null;
            }
        }
        $this->original = $this->value;
        $this->value = $value;
        return $value;
    }

    public function size($width, $height = null)
    {
        if (!isset($height)) {
            $height = round($width * .75);
        }
        $this->width = $width;
        $this->height = $height;
        return $this;
    }

    public function width()
    {
        return $this->width;
    }

    public function height()
    {
        return $this->height;
    }

    public function index($i = null)
    {
        if (isset($i)) {
            $this->index = $i;
        }
        return $this->index;
    }

    public function source($c = null)
    {
        if (isset($c)) {
            $this->source = $c;
        }
        return $this->source;
    }
}

