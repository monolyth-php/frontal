<?php

namespace monolyth\account\render;

class Delete_Breadcrumb_Finder extends Breadcrumb_Finder
{
    public function all(array $args)
    {
        return parent::all($args) + [
            $this->url('monolyth\account\delete') => 
                $this->text('monolyth\account\delete'),
        ];
    }
}
