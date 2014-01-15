<?php

namespace monolyth;

trait User_Access
{
    public function user()
    {
        static $user;
        if (!isset($user)) {
            $user = new User_Model;
        }
        return $user;
    }
}

