<?php

namespace monolyth\render;
use monolyth\Controller;
use monolyth\Login_Required;
use monolyth\HTTP404_Exception;

class Tmp_Media_Controller extends Controller implements Login_Required
{
    protected function get(array $args)
    {
        if (!isset($_GET['f'])) {
            throw new HTTP404_Exception();
        }
        $_GET['f'] = urldecode($_GET['f']);
        try {
            $i = getimagesize($_GET['f']);
        } catch (ErrorException $e) {
            throw new HTTP404_Exception();
        }
        $this->template = null;
        return $this->view(
            'page/media/tmp',
            ['imagefile' => $_GET['f']] + $args + compact('i')
        );
    }
}

