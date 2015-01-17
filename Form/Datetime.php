<?php

namespace monolyth\render\form;

class Datetime extends Text
{
    const ERROR_DATENOTINPAST = 'notinpast';
    const ERROR_DATENOTINFUTURE = 'notinfuture';
    const ERROR_MIN = 'min';
    const ERROR_MAX = 'max';

    protected $type = 'datetime';
    protected $format = 'Y-m-d H:i:s';

    public function __set($name, $value)
    {
        if ($name != 'value') {
            return null;
        }
        if (!($value = strtotime($value))) {
            $value = null;
        } else {
            $value = date($this->format, $value);
        }
        return parent::__set($name, $value);
    }

    public function isValidDate()
    {
        $error = self::ERROR_INVALID;
        return $this->addTest(function($value) use ($error) {
            if (!$value) {
                return null;
            }
            return strtotime($value) ? null : $error;
        });
    }

    public function isDateInPast()
    {
        $error = self::ERROR_DATENOTINPAST;
        return $this->addTest(function($value) use ($error) {
            return strtotime($this->value) < time() ? null : $error;
        });
    }

    public function isDateInFuture()
    {
        $error = self::ERROR_DATENOTINFUTURE;
        return $this->addTest(function($value) use ($error) {
            $e = $this->getElements();
            return strtotime($this->value) > time() ? null : $error;
        });
    }

    public function min($value)
    {
        $this->renderOptions[] = 'min';
        $this->options['min'] = $value;
        $error = self::ERROR_MIN;
        return $this->addTest(function($value) use ($error) {
            return $this->value >= $this->options['min'] ? null : $error;
        });
    }

    public function max($value)
    {
        $this->renderOptions[] = 'max';
        $this->options['max'] = $value;
        $error = self::ERROR_MAX;
        return $this->addTest(function($value) use ($error) {
            return $this->value <= $this->options['max'] ? null : $error;
        });
    }
}

