<?php

namespace monolyth\admin;
use monad\core\Form;

class Template_Mail_Form extends Form
{
    public function __construct($id = null)
    {
        parent::__construct();
        return parent::prepare($id);
    }
}

