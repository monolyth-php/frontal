<?php

namespace monolyth\account;
use monolyth\Controller;
use monolyth\Login_Required;

class Unconfirmed_Controller extends Controller implements Login_Required
{
    protected function get(array $args)
    {
        return $this->view('page/unconfirmed');
    }
}

