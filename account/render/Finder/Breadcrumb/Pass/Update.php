<?php

namespace monolyth\account\render;

class Update_Pass_Breadcrumb_Finder extends Breadcrumb_Finder
{
    public function all(array $args)
    {
        return parent::all($args) + [
            $this->url('monolyth\account\Update_Pass') => 
                $this->text('monolyth\account\pass/update/title'),
        ];
    }
}
