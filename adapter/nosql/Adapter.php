<?php

namespace monolyth\adapter\nosql;

interface Adapter
{
    public function get($key);
    public function set($key, $value);
    public function delete($key);
}

