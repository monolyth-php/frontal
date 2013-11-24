<?php

/**
 * @package monolyth
 * @subpackage core
 * @subpackage model
 */

namespace monolyth\core\model;
use monolyth\db;

class Table extends ModelAbstract
{
    public function __construct($table)
    {
        $this->table = $table;
        parent::__construct(db\DB::i()->describe($table));
    }
}

