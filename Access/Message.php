<?php

namespace monolyth;

trait Message_Access
{
    public static function message()
    {
        static $message;
        if (!isset($message)) {
            $message = new Message;
        }
        return $message;
    }
}

