<?php

namespace monolyth\admin;
use monad\core\Form;

class Media_Form extends Form
{
    public function __construct($id = null)
    {
        parent::__construct($id);
        return parent::prepare($id);
    }
}

