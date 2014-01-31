<?php

namespace monolyth;

class Confirm_Form extends core\Post_Form
{
    public function __construct()
    {
        parent::__construct();
        $this->addHidden('id')->isRequired();
        $this->addHidden('hash')->isRequired();
    }
}

