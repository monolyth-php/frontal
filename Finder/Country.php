<?php

namespace monolyth;
use Adapter_Access;
use monolyth\adapter\sql\NoResults_Exception;

class Country_Finder implements Finder
{
    use Adapter_Access;
    use Language_Access;

    protected $table = 'monolyth_country c
                        JOIN monolyth_country_i18n ci USING(id)';

    public function all($language = null)
    {
        if (!isset($language)) {
            $language = self::language()->current->id;
        }
        if ($cache = self::cache()) {
            try {
                return $cache->get("countries/$language");
            } catch (adapter\nosql\KeyNotFound_Exception $e) {
            }
        }
        try {
            $results = self::adapter()->rows(
                $this->table,
                '*',
                compact('language'),
                ['order' => 'title ASC']
            );
        } catch (NoResults_Exception $e) {
            $language = self::language()->get($language);
            if (isset($language->fallback)) {
                $results = $this->all($language->fallback);
            } else {
                $results = null;
            }
        }
        if (isset($cache)) {
            $cache->set("countries/$language", $results);
        }
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
            return self::adapter()->row(
                $this->table,
                '*',
                [
                    'code' => $code,
                    'language' => self::language()->current->id
                ]
            );
        } catch (NoResults_Exception $e) {
            return null;
        }
    }
}

