<?php

namespace monolyth\render\form;

class Email extends Text
{
    protected $type = 'email';

    public function prepare($name, array $options = [])
    {
        parent::prepare($name, $options);
        $this->isEmail();
    }

    public function isEmail()
    {
        $error = self::ERROR_INVALID;
        return $this->addTest(function($value) use ($error) {
            if (!strlen(trim($value))) {
                return null;
            }
            return filter_var($value, FILTER_VALIDATE_EMAIL)
                && preg_match("/.*@.*\..*/", $value) ?
                null : $error;
        });
    }
}

