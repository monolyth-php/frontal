<?php

namespace monolyth\account;

class New_Pass_Form extends Update_Pass_Form
{
    public function __construct()
    {
        parent::__construct();
        unset($this['old']);
    }
}

