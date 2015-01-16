<?php

namespace monolyth;

class View
{
    protected $filename;

    public function __construct($language, $filename)
    {
        $this->filename = $filename;
    }

    public function __invoke()
    {
        include $this->filename;
    }
}

