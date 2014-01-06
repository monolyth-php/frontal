<?php

namespace monolyth\admin;
use monad\core;
use monolyth\adapter\sql\NoResults_Exception;

class Template_Mail_Finder extends core\Finder
{
    public function all($size, $page, array $where = [], array $options = [])
    {
        $options += [
            'limit' => $size,
            'offset' => ($page - 1) * $size,
        ];
        try {
            return self::adapter()->pages(
                'monolyth_mail_template',
                ['CONCAT(id, language) AS id', 'language', 'description'],
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
            $model = new Template_Mail_Model;
            return $model->load(self::adapter()->row(
                'monolyth_mail_template',
                '*',
                $where
            ));
        } catch (NoResults_Exception $e) {
            return null;
        }
    }
}

