<?php

namespace monolyth;

abstract class Crud_Controller extends Controller
{
    protected function get($action)
    {
        $args = func_get_args();
        $action = array_shift($args);
        $fn = "get$action";
        return $this->$fn($args);
    }

    protected function post($action)
    {
        $args = func_get_args();
        $action = array_shift($args);
        $fn = "post$action";
        return $this->$fn($args);
    }

    abstract protected function getCreate();
    abstract protected function postCreate();
    abstract protected function getUpdate();
    abstract protected function postUpdate();
    abstract protected function getDelete();
    abstract protected function postDelete();
}

