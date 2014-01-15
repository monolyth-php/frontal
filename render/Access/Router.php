<?php

namespace monolyth\render;
use monolyth\Monolyth;

trait Router_Access
{
    protected static $router;

    public static function router()
    {
        return Monolyth::router();
    }
}

