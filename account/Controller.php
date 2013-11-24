<?php

namespace monolyth\account;
use monolyth;

class Controller extends monolyth\Controller implements monolyth\Login_Required
{
    protected function get()
    {
        return $this->view('page/default');
    }
}

