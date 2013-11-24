<?php

namespace monolyth\core;
use monolyth\render\Static_Helper;
use monolyth\Project_Access;

abstract class External implements Project_Access
{
    use Static_Helper;

    protected $files = [];

    public function push($file)
    {
        $this->files[] = func_get_args();
    }

    public function unshift($file)
    {
        array_unshift($this->files, func_get_args());
    }
}

