<?php

namespace monolyth;
use monolyth\Finder;
use monolyth\adapter;
use monolyth\Language_Access;
use monolyth\adapter\sql\NoResults_Exception;

class Country_Finder
implements Finder, adapter\Access, Language_Access, Cache_Access
{
    protected $table = 'monolyth_country c
                        JOIN monolyth_country_i18n ci USING(id)';

    public function all($language = null)
    {
        if (!isset($language)) {
            $language = $this->language->current->id;
        }
        try {
            return $this->cache->get("countries/$language");
        } catch (adapter\nosql\KeyNotFound_Exception $e) {
        }
        try {
            $results = $this->adapter->rows(
                $this->table,
                '*',
                compact('language'),
                ['order' => 'title ASC']
            );
        } catch (NoResults_Exception $e) {
            $language = $this->language->get($language);
            if (isset($language->fallback)) {
                $results = $this->all($language->fallback);
            } else {
                $results = null;
            }
        }
        $this->cache->set("countries/$language", $results);
        return $results;
    }

    public function select($nullable = true)
    {
        if ($countries = $this->all()) {
            $return = $nullable ? ['' => null] : [];
            foreach ($countries as $country) {
                $return[$country['id']] = $country['title'];
            }
            return $return;
        }
    }

    public function find($code)
    {
        try {
            return $this->adapter->row(
                $this->table,
                '*',
                [
                    'code' => $code,
                    'language' => $this->language->current->id
                ]
            );
        } catch (NoResults_Exception $e) {
            return null;
        }
    }
}

