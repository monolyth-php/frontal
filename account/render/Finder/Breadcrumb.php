<?php

namespace monolyth\account\render;
use monolyth\render;

class Breadcrumb_Finder extends render\Breadcrumb_Finder
{
    public function all(array $args)
    {
        return parent::all($args) + [
            $this->url('monolyth\account') => 
                $this->text('monolyth\account\title'),
        ];
    }
}
