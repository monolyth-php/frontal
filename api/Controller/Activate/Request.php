<?php

namespace monolyth\api;
use monolyth\account;
use monolyth\HTTP301_Exception;
use monolyth\render\FileNotFound_Exception;
use ErrorException;

class Request_Activate_Controller extends account\Request_Activate_Controller
{
    protected $template = false;

    protected function post(array $args)
    {
        if ($error = $this->activate->request(self::user()->id())) {
            return $this->view('monolyth\render\json', ['data' => false]);
        }
        self::message()->add('success', './success');
        return $this->view('monolyth\render\json', ['data' => true]);
    }
}

