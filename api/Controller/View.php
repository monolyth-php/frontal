<?php

namespace monolyth\api;
use monolyth\Controller;
use monolyth\HTTP404_Exception;

class View_Controller extends Controller
{
    protected $template = false;

    protected function get(array $args)
    {
        extract($args);
        return $this->view("\\$viewname", ['parse' => true]);
    }
}

