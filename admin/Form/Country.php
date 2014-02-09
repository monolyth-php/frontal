<?php

namespace monolyth\admin;
use monad\core\I18n_Form;
use monolyth\Language_Access;

class Country_Form extends I18n_Form implements Language_Access
{
    public function __construct($id = null)
    {
        parent::__construct($id);
        return parent::prepare($id);
    }
}

