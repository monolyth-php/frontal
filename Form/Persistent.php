<?php

namespace monolyth\Form;

use monolyth\Session_Access;

class Persistent extends Post
{
    use Session_Access;

    public function __construct()
    {
        parent::__construct();
        if (is_null(self::session()->get('Form'))) {
            self::session()->set('Form', []);
        }
        self::session()->set('Form', $_POST + self::session()->get('Form'));
        $this->addSource(self::session()->get('Form'));
        return parent::prepare();
    }
}

