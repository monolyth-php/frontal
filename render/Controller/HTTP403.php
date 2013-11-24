<?php

/**
 * @package monolyth
 * @subpackage render
 */

namespace monolyth\render;
use monolyth\Controller;
use monolyth\core\HTTPError;

class HTTP403_Controller extends Controller implements HTTPError
{
    protected function get()
    {
        header("HTTP/1.1 403 Forbidden", true, 403);
        header("Content-type: text/html", true); // might have been overridden
        return $this->view('page/http403');
    }
}

