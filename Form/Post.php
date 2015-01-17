<?php

namespace monolyth\Form;

use monolyth\Form;

abstract class Post extends Form
{
    public function __construct($id = null)
    {
        parent::__construct($id);
        $this->addSource($_POST);
    }

    public function cancelled()
    {
        return isset($_POST['act_cancel']);
    }
}

