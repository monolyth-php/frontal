<?php

/**
 * @package monolyth
 * @subpackage render
 */

namespace monolyth\render;
use monolyth\Controller;
use monolyth\core\HTTPError;

class HTTP401_Controller extends Controller implements HTTPError
{
    protected function get()
    {
        header("HTTP/1.1 401 Not authorized", true, 401);
        header("Content-type: text/html", true); // might have been overridden
        return $this->view('page/http401');
    }
}

