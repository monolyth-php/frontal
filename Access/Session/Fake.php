<?php

namespace monolyth;

trait Fake_Session_Access
{
    public function session()
    {
        static $session;
        if (!isset($session)) {
            $session = Fake_Session_Model::instance();
        }
        return $session;
    }
}

