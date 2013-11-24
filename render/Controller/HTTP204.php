<?php

namespace monolyth\render;
use monolyth\core\Controller;

class HTTP204_Controller extends Controller
{
    protected function get()
    {
        header("HTTP/1.1 204 No Content", true, 204);
        die();
    }
}

