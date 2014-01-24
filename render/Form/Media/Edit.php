<?php

namespace monolyth\render;
use monolyth\core\Post_Form;

class Edit_Media_Form extends Post_Form
{
    public function __construct()
    {
        parent::__construct();
        $this->addFile('media');
        return parent::prepare();
    }
}

