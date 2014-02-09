<?php

namespace monolyth;

trait Session_Access
{
    public function session()
    {
        static $session;
        if (!isset($session)) {
            $session = new PHP_Session_Model;
        }
        return $session;
    }
}

