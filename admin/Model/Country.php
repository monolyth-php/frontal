<?php

namespace monolyth\admin;
use monad\core\Model;
use monolyth\adapter\sql\InsertNone_Exception;
use monolyth\adapter\sql\UpdateNone_Exception;
use monolyth\adapter\sql\DeleteNone_Exception;
use monolyth\render\form\Info;
use monad\admin\I18n_Model;

class Country_Model extends Model
{
    use I18n_Model;

    public $requires = ['monolyth_country', 'monolyth_country_i18n'];

    public function save(Country_Form $form)
    {
        $id = isset($this['id']) ? $this['id'] : null;
        if (!($error = $this->saveI18n($form, 'monolyth_country'))) {
            return null;
        }
        return $error;
    }

    public function delete()
    {
        try {
            self::adapter()->delete('monolyth_country', ['id' => $this['id']]);
            return null;
        } catch (DeleteNone_Exception $e) {
            return 'delete';
        }
    }
}

