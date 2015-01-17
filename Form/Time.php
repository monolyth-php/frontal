<?php

namespace monolyth\render\form;
use monolyth\core\Element;

class Time extends Element
{
    protected $type = 'time';

    public function isValidTime()
    {
        $error = self::ERROR_INVALID;
        $self = $this;
        return $this->addTest(function($value) use ($error, $self) {
            $current = str_replace(':', '', $self->value);
            return $current > '000000' && $current < '235959' ? null : $error;
        });
    }

    public function isTimeInPast()
    {
        $error = self::ERROR_DATENOTINPAST;
        $self = $this;
        return $this->addTest(function($value) use ($error, $self) {
            return str_replace(':', '', $self->value) < date('His') ?
                null :
                $error;
        });
    }

    public function isTimeInFuture()
    {
        $error = self::ERROR_DATENOTINFUTURE;
        $self = $this;
        return $this->addTest(function($value) use ($error, $self) {
            return str_replace(':', '', $self->value) > date('His') ?
                null :
                $error;
        });
    }
}

