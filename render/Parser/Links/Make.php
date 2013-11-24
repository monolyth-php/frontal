<?php

/**
 * Parse certain keywords/sentences into links.
 *
 * @package monolyth
 * @subpackage render
 * @author Marijn Ophorst <marijn@monomelodies.nl>
 * @copyright MonoMelodies 2008, 2009, 2012
 */

namespace monolyth\render;

class Make_Links_Parser extends Parser
{
    /**
     * Do the parsing.
     *
     * @param string $html The HTML to parse.
     * @param array $array Array of keywords/links. The keywords may contain
     *                     regular expressions.
     */
    public function __invoke($html, $array)
    {
        $matches = array_keys($array);
        $urlreplacements = array_values($array);
        // do this only for the body
        $body = $this->body($html);
        $parts = $this->textnodes($body);
        foreach ($urlreplacements as &$url) {
            $url = "$1<a href=\"$url\">$2</a>$3";
        }
        foreach ($matches as &$match) {
            $match = "@(\s|>)($match)(\s|[</,;:.!?])@im";
        }
        foreach ($parts as &$part) {
            if (!strlen(trim($part)) || $part{0} == '<') {
                continue;
            }
            $part = preg_replace($matches, $urlreplacements, $part);
        }
        $body = implode('', $parts);
        // replace nested links with their original - they were already links
        $body = $this->remove_double_tags($body, 'a');
        // inject back into the rest of the page
        return $this->html($body);
    }
}

