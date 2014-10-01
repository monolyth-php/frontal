<?php

namespace monolyth\render;
use monolyth\Controller;
use monolyth\Ajax_Login_Required;
use monolyth\HTTP400_Exception;
use monolyth\HTTP500_Exception;

class Upload_Media_Controller extends Controller
{
    protected $template = false;

    protected function post(array $args)
    {
        return $this->view(
            'monolyth\render\json',
            ['data' => ['file' => $_FILES['file']['tmp_name']]]
        );
    }
}

