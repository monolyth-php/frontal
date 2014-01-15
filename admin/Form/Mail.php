<?php

namespace monolyth\admin;
use monad\core\Form;

class Mail_Form extends Form
{
    public function __construct($id = null)
    {
        parent::__construct($id);
        $this->addText('sender', $this->text('./sender'))
             ->maxLength(255)
             ->isRequired();
        $this->addText('subject', $this->text('./subject'))
             ->maxLength(255)
             ->isRequired();
        $this->addTextarea('html', $this->text('./html'));
        $this->addTextarea('plain', $this->text('./plain'));
        return parent::prepare();
    }
}

