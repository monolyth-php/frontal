<?php

namespace monolyth\api;
use monolyth\Controller;
use monolyth\HTTP404_Exception;

class Form_Controller extends Controller
{
    protected $template = false;

    protected function get(array $args)
    {
        extract($args);
        $name = str_replace('-', '\\', $name);
        if (class_exists($name)) {
            $form = new $name;
            $parse = true;
            return $this->view(
                [
                    isset($_GET['view']) ?
                        $_GET['view'] :
                        'monolyth\render\form\table',
                    'monolyth\render\form\form',
                ],
                compact('form', 'parse')
            );
        }
        throw new HTTP404_Exception;
    }
}

