<?php

namespace monolyth\render;
use monolyth\Finder;
use monolyth\Utils;

class Breadcrumb_Finder implements Finder
{
    use Url_Helper, utils\Translatable;

    private $path = [];

    public function history(array $path)
    {
        $this->path = $path;
    }

    public function all()
    {
        return [$this->url('') => $this->text('\home')];
    }
}
