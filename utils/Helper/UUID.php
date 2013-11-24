<?php

/**
 * @package monolyth
 * @subpackage utils
 */

namespace monolyth\utils;

trait UUID_Helper
{
    public function generate()
    {
        $parts = [];
        foreach ([8, 4, 4, 4, 12] as $key => $amount) {
            $str =& $parts[$key];
            if ($key == 2) {
                $str .= '4';
                $amount--;
            } elseif ($key == 3) {
                $str .= dechex(rand(8, 11));
                $amount--;
            }
            for ($i = 0; $i < $amount; $i++) {
                $str .= dechex(rand(0, 15));
            }
        }
        return implode('-', $parts);
    }
}

