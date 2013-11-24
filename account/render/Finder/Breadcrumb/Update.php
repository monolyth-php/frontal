<?php

namespace monolyth\account\render;

class Update_Breadcrumb_Finder extends Breadcrumb_Finder
{
    public function all(array $args)
    {
        return parent::all($args) + [
            $this->url('monolyth\account\update') => 
                $this->text('monolyth\account\update'),
        ];
    }
}
