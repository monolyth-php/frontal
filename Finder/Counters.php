<?php

namespace monolyth;

class Counters_Finder implements Finder, adapter\Access
{
    public function find($name)
    {
        try {
            return self::adapter()->field(
                'monolyth_counters',
                'value',
                compact('name')
            );
        } catch (adapter\sql\NoResults_Exception $e) {
            return 0;
        }
    }

    public function all()
    {
        $counters = [];
        try {
            foreach (self::adapter()->rows(
                'monolyth_counters',
                '*',
                [],
                ['order' => 'LOWER(name) ASC']
            ) as $row) {
                $counters[$row['name']] = $row['value'];
            }
        } catch (adapter\sql\NoResults_Exception $e) {
        }
        return $counters;
    }
}

