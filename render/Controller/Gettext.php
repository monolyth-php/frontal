<?php

namespace monolyth\render;
use monolyth\core\Controller;
use monolyth\Ajax_Required;

class Gettext_Controller extends Controller implements Ajax_Required
{
    protected function get(array $args)
    {
        $this->template = null;
        $data = [];
        foreach ($_POST['ids'] as $id) {
            $data[] = [$id, $this->text($id)];
        }
        $parse = true;
        $view = $this->view(
            'monolyth\render\page/json',
            compact('data', 'parse')
        );
        return $view;
    }
}

