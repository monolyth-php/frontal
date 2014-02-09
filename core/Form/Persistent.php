<?php

namespace monolyth\core;
use monolyth\Session_Access;

class Persistent_Form extends Post_Form
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

