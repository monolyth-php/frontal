<?php

namespace monolyth\adapter\nosql;

class Dummy implements Cache, Adapter
{
    public function get($key)
    {
        throw new KeyNotFound_Exception($key);
    }

    public function set($key, $value, $expiration = 0)
    {
        return false;
    }

    public function delete($key)
    {
        throw new KeyNotFound_Exception($key);
    }

    public function stats()
    {
        return ['total' => 0, 'time' => 0];
    }
}

