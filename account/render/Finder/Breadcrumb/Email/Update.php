<?php

namespace monolyth\account\render;

class Update_Email_Breadcrumb_Finder extends Breadcrumb_Finder
{
    public function all(array $args)
    {
        return parent::all($args) + [
            $this->url('monolyth\account\Update_Email') => 
                $this->text('monolyth\account\email/update/title'),
        ];
    }
}
