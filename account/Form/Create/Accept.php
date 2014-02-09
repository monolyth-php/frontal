<?php

namespace monolyth\account;
use monolyth\core\Post_Form;

class Accept_Create_Form extends Post_Form
{
    public function __construct()
    {
        parent::__construct();
        $this->addRadios(
            'terms',
            $this->text('./terms'),
            [1 => $this->text('./yes'), 0 => $this->text('./no')]
        );
        $this->addButton(self::BUTTON_SUBMIT, $this->text('create/next'));
        $this->addButton(
            self::BUTTON_SUBMIT,
            $this->text('create/previous'),
            'act_previous'
        );
        return parent::prepare();
    }
}

