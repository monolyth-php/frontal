<?php

namespace monolyth;
use monolyth\Finder;
use monolyth\adapter;
use monolyth\Language_Access;
use monolyth\Country_Access;
use monolyth\adapter\sql\NoResults_Exception;

class City_Finder
implements Finder, adapter\Access, Language_Access, Country_Access
{
    public function all($country = null, $language = null)
    {
        if (!isset($country)) {
            $country = $this->country->current->id;
        }
        if (!isset($language)) {
            $language = $this->language->current->id;
        }
        try {
            return $this->adapter->rows(
                'monolyth_city',
                '*',
                [
                    'country' => $country,
                    [
                        ['language' => $language],
                        ['language' => null],
                    ],
                ],
                ['order' => 'name ASC']
            );
        } catch (NoResults_Exception $e) {
            return null;
        }
    }

    public function select($nullable = true)
    {
        $return = $nullable ? ['' => null] : [];
        if ($cities = $this->all()) {
            foreach ($cities as $city) {
                $return[$city['id']] = $city['name'];
            }
        }
        return $return;
    }

    public function find($name, $country = null, $language = null)
    {
        try {
            $where = compact('name');
            if (isset($country)) {
                $where += compact('country');
            }
            if (isset($language)) {
                $where += compact('language');
            }
            return $this->adapter->row('monolyth_city', '*', $where);
        } catch (NoResults_Exception $e) {
            return null;
        }
    }
}

