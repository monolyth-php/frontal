<?php

namespace monolyth\render;

trait Email_Access
{
    public static function email()
    {
        static $email;
        if (!isset($email)) {
            $email = Email::instance();
        }
        return $email;
    }
}

