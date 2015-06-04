<?php

/**
 * Utilities for writing ECMAscript.
 *
 * @package monolyth
 * @subpackage utils
 * @author Marijn Ophorst <marijn@monomelodies.nl>
 * @copyright MonoMelodies 2008, 2009, 2010, 2011, 2012
 */
namespace monolyth\utils;

/** The main class. */
abstract class Script
{
    const EXTERNAL = 1;
    const INLINE = 2;

    private $externals = [];
    private $inlines = [];
    private $outers = [];

    /**
     * Return a string escaped for use in Javascript.
     *
     * @param string $string The string to escape.
     * @return string An escaped string.
     */
    public static function escape($string)
    {
        return str_replace(
            ["\\", "'",  "\n", '</', '&#39;', "\r"],
            ["\\\\", "\\'", "\\n", '<\\/', "\\'", ''],
            $string
        );
    }

    /**
     * Return multiple javascript-objects based on $array.
     * The keys of the top-level are used as variable names.
     *
     * @param array $array The array to convert.
     * @return string A string containing javascript-objects.
     */
    public static function arrayToObjects($array)
    {
        $returnvalue = [];
        foreach ($array as $arrayname => $value) {
            $returnvalue[] = sprintf(
                'var %s = %s;',
                $arrayname,
                json_encode($value, JSON_FORCE_OBJECT)
            );
        }
        return implode("\n", $returnvalue)."\n";
    }
}

