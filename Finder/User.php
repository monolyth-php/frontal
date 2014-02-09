<?php

namespace monolyth;
use Adapter_Access;

class User_Finder implements Finder
{
    use Adapter_Access;
    use core\Singleton;

    public function find($nameOrId)
    {
        try {
            $where = ['LOWER(name)' => strtolower($nameOrId)];
            if (is_numeric($nameOrId)) {
                $where = [[$where, ['id' => $nameOrId]]];
            }
            return self::adapter()->row('monolyth_auth', '*', $where);
        } catch (adapter\sql\NoResults_Exception $e) {
            return null;
        }
    }

    public function idByName($name)
    {
        try {
            return self::adapter()->field(
                'monolyth_auth',
                'id',
                ['LOWER(name)' => strtolower($name)]
            );
        } catch (adapter\sql\NoResults_Exception $e) {
            return null;
        }
    }
}

