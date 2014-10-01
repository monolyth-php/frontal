<?php

namespace monolyth\render;
use monolyth\Controller;
use monolyth\Ajax_Login_Required;
use monolyth\HTTP400_Exception;
use monolyth\HTTP500_Exception;
use monolyth\Config;

class Upload_Media_Controller extends Controller
{
    protected $template = false;

    protected function post(array $args)
    {
        $config = Config::get('monolyth');
        $name = substr(
            $_FILES['file']['tmp_name'],
            strrpos($_FILES['file']['tmp_name'], '/') + 1
        );
        // Todo: cleanup old files in this directory, anything older than
        // 24 hours can be considered obsolete by now...
        move_uploaded_file(
            $_FILES['file']['tmp_name'],
            "{$config->uploadPath}/$name"
        );
        return $this->view(
            'monolyth\render\json',
            ['data' => ['file' => "{$config->uploadPath}/$name"]]
        );
    }
}

