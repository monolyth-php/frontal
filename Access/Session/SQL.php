<?php

namespace monolyth;

trait SQL_Session_Access
{
    public function session()
    {
        static $session;
        if (!isset($session)) {
            $session = new SQL_Session_Model;
        }
        return $session;
    }
}

