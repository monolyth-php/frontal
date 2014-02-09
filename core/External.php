<?php

namespace monolyth\core;
use monolyth\render\Static_Helper;

abstract class External
{
    use Static_Helper;

    protected $project;

    public function __construct($project)
    {
        $this->project = $project;
    }

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

