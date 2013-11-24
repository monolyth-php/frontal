<?php

/**
 * @package monolyth
 * @subpackage core
 * @subpackage model
 */

namespace monolyth\core\model;
use monolyth\core;

class Tree extends \ArrayObject
{
    private $self;

    public function __construct(core\Model $model = null, \ArrayObject $group,
        $compare = 'parent', $to = 'id')
    {
        $this->self = $model;
        $this->compare = $compare;
        $this->to = $to;
        $adds = array();
        foreach ($group->getArrayCopy() as $i => $item) {
            if (
                !isset($model) && is_null($item->$compare)
                || (isset($model) && $model->$to == $item->$compare)
                || (isset($model)
                    && $item->$compare instanceof core\Model
                    && $model->$to == $item->$compare->$to
                )
            ) {
                $adds[] = $group[$i];
                unset($group[$i]);
            }
        }
        $items = array();
        foreach ($adds as $add) {
            $items[] = new static($add, $group, $compare, $to);
        }
        unset($group);
        unset($adds);
        if (count($items)) {
            parent::__construct($items);
        } else {
            parent::__construct(array());
        }
    }

    public function __call($fn, $args)
    {
        return call_user_func_array(array($this->self, $fn), $args);
    }

    public static function __callStatic($fn, $args)
    {
        $class = get_class($this->self);
        return call_user_func_array(array($class, $fn), $args);
    }

    public function __get($name)
    {
        return $this->self->$name;
    }

    public function __set($name, $value)
    {
        $this->self->$name = $value;
    }

    public function __isset($name)
    {
        return isset($this->self->$name);
    }

    public function __unset($name)
    {
        unset($this->self->$name);
    }
}

