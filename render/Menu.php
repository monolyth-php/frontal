<?php

/**
 * The Menu class provides a generic way to deal with simple or complex menus
 * on web projects. It supports an infinite amount of submenus and in theory
 * can build a menu from any data structure.
 *
 * @package monolyth
 * @subpackage render
 */

namespace monolyth\render;
use ArrayObject;
use StdClass;
use ErrorException;

/**
 * The Menu class. It should be constructed with an iterable object, or a
 * simple array.
 *
 * The default behaviour is to expect query results from monad_menu, or a
 * compatible form.
 *
 * @see monolyth\render\Item_Menu
 */
class Menu extends ArrayObject
{
    const ITERATE = 1;
    const KEY = 2;
    const VALUE = 3;

    public function build($items, array $options = [])
    {
        $options += [
            self::ITERATE => null,
            self::KEY => function($key) { return $key; },
            self::VALUE => function($value) {
                return $this->item->build($value);
            },
        ];
        if (!isset($options[self::ITERATE])) {
            $options[self::ITERATE] = function($key, $value) use($options) {
                $key = call_user_func($options[self::KEY], $key);
                $value = call_user_func($options[self::VALUE], $value);
                return [$key, $value];
            };
        }
        foreach ($items as $key => $value) {
            list($key, $value) = call_user_func(
                $options[self::ITERATE],
                $key,
                $value
            );
            $this[$key] = $value;
        }
        return $this;
    }
}

