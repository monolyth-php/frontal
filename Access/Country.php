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
}

