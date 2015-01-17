<?php

namespace monolyth\render\form;

class Url extends Text
{
    protected $type = 'url';

    public function prepare($name, array $options = [])
    {
        parent::prepare($name, $options);
        $this->setPlaceholder('http://');
        $this->isUrl();
    }

    public function isUrl()
    {
        $error = self::ERROR_INVALID;
        return $this->addTest(function($value) use ($error) {
            if (!strlen(trim($value))) {
                return null;
            }
            return filter_var($value, FILTER_VALIDATE_URL) ? null : $error;
        });
    }

    public function __set($name, $value)
    {
        if ($name != 'value') {
            return;
        }
        if (strlen(trim($value)) &&
            !preg_match("@^(https?|ftp)://@", $value)
        ) {
            $value = "http://$value";
        }
        return parent::__set($name, $value);
    }
}

