<?php

namespace monolyth\admin;
use monad\core\Finder;

class Language_Finder extends Finder
{
    public function all($size, $page, array $where = [], array $options = [])
    {
        return self::adapter()->pages(
            'monolyth_language',
            '*',
            $where,
            $options + [
                'limit' => $size,
                'offset' => ($page - 1) * $size,
                'order' => 'sortorder ASC',
            ]
        );
            
    }

    public function find(array $where)
    {
        try {
            return (new Language_Model)->load(self::adapter()->row(
                'monolyth_language',
                '*',
                $where
            ));
        } catch (NoResults_Exception $e) {
            return new Language_Model;
        }
    }
}

