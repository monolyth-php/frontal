<?php

/**
 * Parse keywords into <abbr title="longer description">keyword</abbr>.
 *
 * @package monolyth
 * @subpackage render
 * @author Marijn Ophorst <marijn@monomelodies.nl>
 * @copyright MonoMelodies 2008, 2009, 2010, 2012
 */
namespace monolyth\render;

class Abbreviations_Parser extends Parser
{
    public function __invoke($html, $array)
    {
        $abbreviations = array_keys($array);
        $titles = array_values($array);
        // do this only for the body
        $body = $this->body($html);
        $index = [];
        foreach ($abbreviations as $i => $abbr) {
            $index[strtolower($abbr)] = $titles[$i];
        }
        $parts = $this->textnodes($body);
        foreach ($parts as &$part) {
            if (!strlen(trim($part)) or $part{0} == '<') {
                continue;
            }
            $part = preg_replace(
                "@(".implode('|', $abbreviations).")@ime",
                "'<abbr title=\"'.\$index[strtolower('$1')].'\">$1</abbr>'",
                $part
            );
        }
        $body = implode('', $parts);
        $body = $this->remove_double_tags($body, 'abbr');
        // inject back into the rest of the page
        return $this->html($body);
    }
}

