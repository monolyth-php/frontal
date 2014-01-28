<?php

namespace monolyth\admin;
use monad\core;
use monolyth\adapter\sql\NoResults_Exception;

class Country_Finder extends core\I18n_Finder
{
    public function all($size, $page, array $where = [], array $options = [])
    {
        $options += [
            'limit' => $size,
            'offset' => ($page - 1) * $size,
            'order' => 'title ASC',
        ];
        try {
            return self::adapter()->pages(
                $this->table('monolyth_country', 'monolyth_country_i18n')
               .sprintf(
                    " JOIN monolyth_language l ON %s = l.id ",
                    implode('', $this->fields([], 'language', false))
                ),
                $this->fields(
                    ['monolyth_country.*', 'l.title AS language_str'],
                    ['title']
                ),
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
            return (new Country_Model)->load(self::adapter()->row(
                'monolyth_country',
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
            return self::adapter()->rows('monolyth_country_i18n', '*', $where);
        } catch (NoResults_Exception $e) {
            return null;
        }
    }
}

