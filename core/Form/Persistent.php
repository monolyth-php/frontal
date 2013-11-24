<?php

namespace monolyth\core;
use monolyth\Session_Access;

class Persistent_Form extends Post_Form implements Session_Access
{
    public function prepare()
    {
        if (is_null($this->session->get('Form'))) {
            $this->session->set('Form', []);
        }
        $this->session->set('Form', $_POST + $this->session->get('Form'));
        $this->addSource($this->session->get('Form'));
        return parent::prepare();
    }
}

