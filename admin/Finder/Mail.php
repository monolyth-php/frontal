<?php

namespace monolyth\admin;
use monad\core;
use monolyth\adapter\sql\NoResults_Exception;
use adapter\Access as Adapter_Access;

class Mail_Finder extends core\Finder
{
    use Adapter_Access;

    public function all($size, $page, array $where = [], array $options = [])
    {
        $options += [
            'limit' => $size,
            'offset' => ($page - 1) * $size,
        ];
        try {
            return self::adapter()->pages(
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
            $model = new Mail_Model;
            return $model->load(self::adapter()->row(
                'monolyth_mail',
                '*',
                $where
            ));
        } catch (NoResults_Exception $e) {
            return null;
        }
    }
}

