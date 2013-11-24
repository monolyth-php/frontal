<?php

/**
 * @package monolyth
 * @subpackage monad
 */
namespace monolyth\monad;
use monad\monad, monolyth\db;

class Language extends monad\Monad
{
    const TOSTRING = 'title';
    public $listFields = ['code', 'title'];

    public function __construct()
    {
        parent::__construct();
        $this->listQuery = function(array $where, array $options = []) {
            return db\DB::getRows(
                'monolyth_language JOIN monolyth_language_all USING(id)',
                '*',
                $where,
                $options
            );
        };
    }
}

