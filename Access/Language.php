<?php

namespace monolyth;

trait Language_Access
{
    public static function language()
    {
        static $language;
        if (!isset($language)) {
            $language = new Language_Model;
        }
        return $language;
    }
}

