<?php

namespace monolyth\core\model;
use monolyth\core;

abstract class Link extends core\Model
{
    private $linked;

    public function __construct(array $definition)
    {
        $name = array_shift(explode('_Link_', get_class($this)));
        $this->linked = new $name();
        $this->import($this->linked);
        $this->fields = '*';
        parent::__construct($definition);
    }

    public function getTable()
    {
        static $table;
        if (isset($table)) {
            return $table;
        }
        foreach ($this->schema as $key => $data) {
            if (!isset($data->references)) {
                continue;
            }
            if ($data->references[0] == get_class($this->linked)) {
                break;
            }
        }
        $me = parent::getTable();
        $base = array_shift(explode(' ', $me));
        $them = parent::getTable($this);
        return $table = "$me JOIN $them ON
            $them.$key = $base.{$data->references[1]}";
    }

    public function getRealTable()
    {
        return parent::getTable($this->linked);
    }

    public function getLinkTable()
    {
        return parent::getTable($this);
    }
}

