<?php

namespace monolyth\render;
use monolyth\Finder;
use monolyth\adapter;
use monolyth\adapter\sql\NoResults_Exception;

class Media_Finder implements Finder, adapter\Access
{
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
            return $this->adapter->rows('monolyth_media', '*', $where);
        } catch (NoResults_Exception $e) {
            return null;
        }
    }

    public function query(array $where)
    {
        try {
            return $this->adapter->row('monolyth_media', '*', $where);
        } catch (NoResults_Exception $e) {
            return null;
        }
    }
}

