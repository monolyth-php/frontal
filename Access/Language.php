<?php

namespace monolyth;

trait Language_Access
{
    public static function language()
    {
        static $language;
        if (!isset($language)) {
            $language = Language_Model::instance();
        }
        return $language;
    }
}

