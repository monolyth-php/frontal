<?php

namespace monolyth;

trait HTTP_Access
{
    public function http()
    {
        static $http;
        if (!isset($http)) {
            $http = new HTTP_Model;
        }
        return $http;
    }
}

