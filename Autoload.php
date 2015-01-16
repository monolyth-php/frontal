<?php

namespace Monolyth;

class Autoload
{
    public function register($prepend = false)
    {
        spl_autoload_register([$this, '__invoke'], true, $prepend);
    }

    public function prefix($prefix)
    {
    }
}

