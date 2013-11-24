<?php

namespace monolyth\utils;
use monolyth\core;
use monolyth\Ajax_Required;

class Store_Cookie_Controller extends core\Controller implements Ajax_Required
{
    protected function post(array $args)
    {
        $this->cookie->store($_POST);
        die();
    }
}

