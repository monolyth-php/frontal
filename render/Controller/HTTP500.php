<?php

/**
 * @package monolyth
 * @subpackage render
 */

namespace monolyth\render;
use monolyth\Controller;
use monolyth\core\HTTPError;

class HTTP500_Controller extends Controller implements HTTPError
{
    protected function get(array $args)
    {
        header("HTTP/1.1 500 Internal Server Error", true, 500);
        header("Content-type: text/html", true); // might have been overridden
        return $this->view('page/http500');
    }
}

