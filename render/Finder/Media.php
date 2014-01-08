<?php

namespace monolyth\render;
use monolyth\Finder;
use adapter\Access as Adapter_Access;
use monolyth\adapter\sql\NoResults_Exception;

class Media_Finder implements Finder
{
    use Adapter_Access;

    public function find($id)
    {
        return $this->query(compact('id'));
    }

    public function all($ids = [])
    {
        try {
            $where = [];
            if ($ids) {
                $where['id'] = ['IN' => $ids];
            }
            return self::adapter()->rows('monolyth_media', '*', $where);
        } catch (NoResults_Exception $e) {
            return null;
        }
    }

    public function query(array $where)
    {
        try {
            return self::adapter()->row('monolyth_media', '*', $where);
        } catch (NoResults_Exception $e) {
            return null;
        }
    }
}

