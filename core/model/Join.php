<?php

/**
 * @package monolyth
 * @subpackage core
 * @subpackage model
 */
namespace monolyth\core\model;
use monolyth\core, monolyth\db;

abstract class Join extends core\Model
{
    public function __construct(array $definition = array())
    {
        parent::__construct($definition);
        $this->table = null;
    }

    public function save()
    {
        throw new db\join\NoSave_Exception(get_class($this));
    }
}

