<?php

namespace monolyth\utils;

trait Translatable
{
    public function text($id)
    {
        return call_user_func_array($this->text, func_get_args());
    }

    public function textLanguage($id, $language)
    {
        return call_user_func_array(
            [$this->text, 'getByLanguage'],
            func_get_args()
        );
    }
}

