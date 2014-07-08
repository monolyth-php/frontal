<?php

namespace monolyth\api;
use monolyth\Controller;
use monolyth\HTTP404_Exception;

class View_Controller extends Controller
{
    protected $template = false;

    protected function get(array $args)
    {
        extract($args);
        $parts = preg_split(
            "@/(page|slice|template)@",
            $viewname,
            3,
            PREG_SPLIT_DELIM_CAPTURE
        );
        if (count($parts) == 3) {
            $viewname = str_replace(
                '/',
                '\\',
                $parts[0]
            ).'\\'.$parts[1].$parts[2];
        } else {
            $viewname = "\\$viewname";
        }
        return $this->view($viewname, ['parse' => true]);
    }
}

