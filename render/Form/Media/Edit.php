<?php

namespace monolyth\render;
use monolyth\core\Post_Form;

class Edit_Media_Form extends Post_Form
{
    public function prepare()
    {
        $this->addFile('media');
        return parent::prepare();
    }
}

