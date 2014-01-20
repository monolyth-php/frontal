<?php

namespace monolyth;

trait Message_Access
{
    public static function message()
    {
        static $message;
        if (!isset($message)) {
            $message = Message::instance();
        }
        return $message;
    }
}

