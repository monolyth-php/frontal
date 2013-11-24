<?php

namespace monolyth\account\render;

class Reset_Pass_Breadcrumb_Finder extends Breadcrumb_Finder
{
    public function all(array $args)
    {
        return parent::all($args) + [
            $this->url('monolyth\account\Reset_Pass') => 
                $this->text('monolyth\account\pass/reset/title'),
        ];
    }
}
