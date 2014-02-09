<?php

namespace monolyth\account;
use monolyth\core\Post_Form;

class Confirm_Delete_Form extends Post_Form
{
    public function __construct()
    {
        parent::__construct();
        $this->addButton(self::BUTTON_CANCEL, $this->text('./cancel'));
        $this->addButton(self::BUTTON_SUBMIT, $this->text('./confirm'));
        return parent::prepare();
    }
}

