<?php

/**
 * A default parser to automagically translate your pages without the need
 * to manually call Text::get ALL OVER THE FRIGGIN' PLACE.
 *
 * @package monolyth
 * @subpackage output
 * @author Marijn Ophorst <marijn@monomelodies.nl>
 * @copyright MonoMelodies 2008, 2009, 2010, 2011, 2012
 */

namespace monolyth\render;

/**
 * The parser-class.
 */
class Script_Translate_Parser extends Translate_Parser
{
    /**
     * Parse the HTML for translatable strings. Translatable strings are
     * identified by matching $translate([data]).
     *
     * "Data" should be a json_encoded string containing an array with textid,
     * language and optional arguments.
     *
     * @param string $html The HTML to parse.
     * @return string The parsed HTML.
     */
    public function __invoke($html)
    {
        while (preg_match_all(
            '@\$translate\((.*?)\)@ms',
            $html,
            $matches
        )) {
            $ids = [];
            foreach ($matches[1] as $i => &$match) {
                try {
                    $decoded = unserialize(base64_decode($match));
                    $match = $decoded;
                } catch (\ErrorException $e) {
                    $match = [$match, null, []];
                }
                $ids[] = $match[0];
                if (strpos($match[0], '\\') === false) {
                    $match[0] = $this->currentNamespace()."\\{$match[0]}";
                }
            }
            $this->text->load($matches[1]);
            foreach ($matches[1] as $i => &$match) {
                $match = call_user_func_array(
                    [$this->text, 'retrieve'],
                    $match
                );
                $match = str_replace(
                    ["\n", '"'],
                    ['\n', '\\"'],
                    $match
                );
            }
            $html = str_replace($matches[0], $matches[1], $html);
        }
        return $html;
    }
}

