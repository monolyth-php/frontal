<?php

namespace monolyth\admin;
use monad\core\Model;
use monolyth\adapter\sql\InsertNone_Exception;
use monolyth\adapter\sql\UpdateNone_Exception;
use monad\admin\I18n_Model;

class Text_Model extends Model
{
    use I18n_Model;

    public $requires = ['monolyth_text', 'monolyth_text_i18n'];

    public function save(Text_Form $form)
    {
        return $this->saveI18n($form, 'monolyth_text');
    }
}

