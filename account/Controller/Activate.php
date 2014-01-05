<?php

namespace monolyth\account;
use monolyth\Controller;
use monolyth\Inactive_Required;

abstract class Activate_Controller extends Controller
implements Inactive_Required
{
    public function __construct()
    {
        parent::__construct();
        $this->form = new Activate_Form;
        $this->activate = new Activate_Model;
    }
}

