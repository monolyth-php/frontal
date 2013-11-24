<?php

namespace monolyth\admin;
use monad\core;
use monolyth\adapter\sql\NoResults_Exception;

class Mail_Finder extends core\Finder
{
    public function all($size, $page, array $where = [], array $options = [])
    {
        $options += [
            'limit' => $size,
            'offset' => ($page - 1) * $size,
        ];
        try {
            return $this->adapter->pages(
                'monolyth_mail',
                [
                    'CONCAT(id, language) AS id',
                    'language',
                    'template',
                    'description',
                    'sender',
                ],
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
            $where['CONCAT(id, language)'] = $where['id'];
            unset($where['id']);
            return $this->model->load($this->adapter->row(
                'monolyth_mail',
                '*',
                $where
            ));
        } catch (NoResults_Exception $e) {
            var_dump($where);
            echo $e->getMessage();
            return null;
        }
    }
}

