<?php

namespace monolyth\admin;
use monad\core\Finder;

class Language_Finder extends Finder
{
    public function all($size, $page = 1)
    {
        return self::adapter()->pages(
            'monolyth_language',
            '*',
            [],
            ['limit' => $size, 'offset' => ($page - 1) * $size]
        );
            
    }
}

