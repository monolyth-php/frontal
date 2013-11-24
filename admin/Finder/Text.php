<?php

namespace monolyth\admin;
use monad\core\I18n_Finder;
use monolyth\adapter\sql\NoResults_Exception;

class Text_Finder extends I18n_Finder
{
    public function all($size, $page, array $where = [], array $options = [])
    {
        try {
            return $this->adapter->pages(
                $this->table('monolyth_text', 'monolyth_text_i18n'),
                $this->fields('monolyth_text.id', 'content'),
                $where,
                $options + [
                    'limit' => $size,
                    'offset' => ($page - 1) * $size
                ]
            );
        } catch (NoResults_Exception $e) {
            return null;
        }
    }

    public function find(array $where)
    {
        try {
            return $this->model->load($this->adapter->row(
                'monolyth_text',
                '*',
                $where
            ));
        } catch (NoResults_Exception $e) {
            return null;
        }
    }

    public function languageData(array $where)
    {
        try {
            return $this->adapter->rows('monolyth_text_i18n', '*', $where);
        } catch (NoResults_Exception $e) {
            return null;
        }
    }
}

