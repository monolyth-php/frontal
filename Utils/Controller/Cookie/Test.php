<?php

namespace monolyth\utils;
use monolyth\core;

class Test_Cookie_Controller extends core\Controller
{
    protected function get(array $args)
    {
        $this->template = false;
        if (!isset($_COOKIE['mocoid'])) {
            (new Cookie_Model)->generateId();
        }
        return $this->view('page/cookie/test');
    }
}

