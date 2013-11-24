<?php

/**
 * @package monolyth
 * @subpackage model
 */

namespace monolyth\model;
use monolyth\db, monolyth\core;

abstract class View extends core\Model
{
    public function save()
    {
        throw new db\join\NoSave_Exception(get_class($this));
    }
}

