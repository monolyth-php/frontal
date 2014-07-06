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
        $name = base64_decode($name);
        if (class_exists($name)) {
            $form = new $name;
            $parse = true;
            return $this->view(
                'monolyth\render\page/json',
                ['data' => ['form' => base64_encode($this->view(
                    [
                        isset($_GET['view']) ?
                            $_GET['view'] :
                            'monolyth\render\form\slice/table',
                        'monolyth\render\form\slice/form',
                    ],
                    compact('form', 'parse')
                )->__invoke())]]
            );
        }
        die('meh');
        throw new HTTP404_Exception;
    }
}

