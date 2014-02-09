<?php

namespace monolyth\account;

class New_Pass_Controller extends Update_Pass_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->form = new New_Pass_Form;
    }
}

