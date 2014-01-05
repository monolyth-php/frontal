<?php

namespace monolyth\utils;
use monolyth\Text_Model;
use monolyth\Language_Access;

trait Translatable
{
    public function text($id)
    {
        static $text;
        if (!isset($text)) {
            $text = new Text_Model($this);
        }
        return call_user_func_array($text, func_get_args());
    }

    public function textLanguage($id, $language)
    {
        static $text;
        if (!isset($text)) {
            $text = new Text_Model($this);
        }
        return call_user_func_array(
            [$text, 'getByLanguage'],
            func_get_args()
        );
    }
}

