<?php

namespace monolyth\utils;
use monolyth\core;

class Store_Cookie_Controller extends core\Controller
{
    protected function post(array $args)
    {
        (new Cookie_Model)->store($_POST);
        die();
    }
}

