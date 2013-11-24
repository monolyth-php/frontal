<?php

namespace monolyth\admin;
use monad\core;
use monolyth\adapter\sql\NoResults_Exception;

class Media_Finder extends core\Finder
{
    public function all($size, $page, array $where = [], array $options = [])
    {
        $options += [
            'limit' => $size,
            'offset' => ($page - 1) * $size,
        ];
        try {
            return $this->adapter->pages(
                'monolyth_media',
                ['id', 'originalname'],
                $where,
                $options
            );
        } catch (NoResults_Exception $e) {
            return null;
        }
    }

    public function find(array $where)
    {
        try {
            return $this->model->load($this->adapter->row(
                'monolyth_media',
                '*',
                $where
            ));
        } catch (NoResults_Exception $e) {
            return null;
        }
    }
}

