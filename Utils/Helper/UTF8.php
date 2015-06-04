<?php

/**
 * @package monolyth
 * @subpackage utils
 */

namespace monolyth\utils;

trait UTF8_Helper
{
    public function toUTF8($string)
    {
        return preg_replace_callback(
            '/&#(\d+);/e',
            function($match) {
                return $this->chrUTF8($match[1]);
            },
            iconv('CP1252', 'UTF-8', html_entity_decode($string))
        );
    }
    
    private function chrUTF8($ord)
    { // php--
        $len = 1 << 7; // space left in first byte
        if ($ord < $len) {
            return chr($ord); // ascii
        }
        $cont = ''; // continuation bytes
        $len >>= 1; // non-ascii header bit
        do {
            $cont .= chr((1 << 7) + $ord % (1 << 6));
            $ord >>= 6;
            $len >>= 1; // add leading bit per byte
        } while ($ord >= $len);
        $ord += (1 << 8) - ($len << 1); // add header = 1(1 x bytes)0
        return chr($ord) . strrev($cont);
    } // shiar++
}

