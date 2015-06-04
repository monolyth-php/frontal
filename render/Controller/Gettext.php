<?php

namespace monolyth\render;
use monolyth\core\Controller;

class Gettext_Controller extends Controller
{
    protected function get(array $args)
    {
        $this->template = false;
        $data = [];
        foreach ($_POST['ids'] as $id) {
            $data[] = [$id, $this->text($id)];
        }
        $parse = true;
        $view = $this->view(
            'monolyth\render\json',
            compact('data', 'parse')
        );
        return $view;
    }
}

