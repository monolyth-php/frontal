<?php

/**
 * @package monolyth
 * @subpackage render
 */

namespace monolyth\render;
use monolyth\Controller;
use monolyth\core\HTTPError;

class HTTP404_Controller extends Controller implements HTTPError
{
    protected function get(array $args)
    {
        header("HTTP/1.1 404 Not Found", true, 404);
        header("Content-type: text/html", true); // might have been overridden
        return $this->view('page/http404');
    }
}

