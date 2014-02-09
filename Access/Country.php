<?php

namespace monolyth;

trait Country_Access
{
    public function country()
    {
        static $country;
        if (!isset($country)) {
            $country = Country_Model::instance();
        }
        return $country;
    }

    public function countries()
    {
        static $countries;
        if (!isset($countries)) {
            $countries = Country_Finder::instance();
        }
        return $countries;
    }
}

