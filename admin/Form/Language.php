<?php

namespace monolyth\admin;
use monad\core\Form;

class Language_Form extends Form
{
    public function __construct($id = null)
    {
        parent::__construct($id);
        $this->addText('title', $this->text('./title'))
             ->isRequired();
        $this->addText('sortorder', $this->text('./sortorder'))
             ->isRequired();
        $this->addCheckbox('is_default', $this->text('./is_default'));
        return parent::prepare($id);
    }
}

