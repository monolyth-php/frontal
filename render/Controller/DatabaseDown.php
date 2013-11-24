<?php

namespace monolyth\render;
use monolyth\core\Controller;

class DatabaseDown_Controller extends Controller
{
    protected function get(array $args)
    {
        try {
            $this->template = $this->view('\template/down');
        } catch (FileNotFound_Exception $e) {
            $this->template = $this->view('monolyth\template/down');
        }
        return $this->view('page/databasedown');
    }
}

