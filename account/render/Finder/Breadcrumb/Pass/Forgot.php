<?php

namespace monolyth\account\render;

class Forgot_Pass_Breadcrumb_Finder extends Breadcrumb_Finder
{
    public function all(array $args)
    {
        return parent::all($args) + [
            $this->url('monolyth\account\Forgot_Pass') => 
                $this->text('monolyth\account\pass/forgot/title'),
        ];
    }
}
