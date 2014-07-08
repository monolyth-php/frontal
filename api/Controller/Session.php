<?php

namespace monolyth\api;
use monolyth\Controller;
use monolyth\HTTP404_Exception;

class Session_Controller extends Controller
{
    protected $template = false;

    protected function get(array $args)
    {
        return $this->view(
            'monolyth\render\page/json',
            ['data' => self::session()->all()]
        );
    }
}

