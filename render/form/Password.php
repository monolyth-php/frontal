<?php

namespace monolyth\render\form;
use monolyth\User_Access;
use ErrorException;

class Password extends Text
{
    protected $type = 'password';

    /** The field must equal the value supplied. */
    public function isEqualTo($hashed)
    {
        try {
            list($hashed, $salt) = func_get_args();
        } catch (ErrorException $e) {
            $salt = null;
        }
        $user = $this->user;
        return $this->addTest(function($value) use($hashed, $salt) {
            if (!strlen(trim($value))) {
                return null;
            }
            try {
                list($hash, $pw) = explode(':', $hashed, 2);
            } catch (ErrorException $e) {
                return $value == $hashed ? null : self::ERROR_NOMATCH;
            }
            $value = "$hash:".hash($hash, $value.$salt);
            return $value == $hashed ? null : self::ERROR_NOMATCH;
        });
    }

    public function __toString()
    {
        $this->value = null;
        return parent::__toString();
    }
}

