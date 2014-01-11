<?php

namespace monolyth;

trait Country_Access
{
    public function country()
    {
        static $country;
        if (!isset($country)) {
            $country = new Country_Model;
        }
        return $country;
    }

    public function countries()
    {
        static $countries;
        if (!isset($countries)) {
            $countries = new Country_Finder;
        }
        return $countries;
    }
}

