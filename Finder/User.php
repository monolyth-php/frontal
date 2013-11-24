<?php

namespace monolyth;

class User_Finder implements Finder, adapter\Access
{
    public function find($nameOrId)
    {
        try {
            $where = ['LOWER(name)' => strtolower($nameOrId)];
            if (is_numeric($nameOrId)) {
                $where = [[$where, ['id' => $nameOrId]]];
            }
            return $this->adapter->row('monolyth_auth', '*', $where);
        } catch (adapter\sql\NoResults_Exception $e) {
            return null;
        }
    }

    public function idByName($name)
    {
        try {
            return $this->adapter->field(
                'monolyth_auth',
                'id',
                ['LOWER(name)' => strtolower($name)]
            );
        } catch (adapter\sql\NoResults_Exception $e) {
            return null;
        }
    }
}

