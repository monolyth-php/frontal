<?php

/**
 * Translates all i18n calls to monolyth\Text_Model and its get-related methods
 * into the corresponding text, or a placeholder if it's missing.
 *
 * @package monolyth
 * @subpackage render
 * @author Marijn Ophorst <marijn@monomelodies.nl>
 * @copyright MonoMelodies 2008, 2009, 2010, 2011, 2012, 2014
 */

namespace monolyth\render;
use monolyth\core\Parser;
use monolyth\Text_Model;
use ErrorException;

/**
 * The parser-class.
 */
class Translate_Parser extends Parser
{
    public function __construct()
    {
        $this->text = new Text_Model($this);
    }

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
        if (strpos($html, '$translate(') === false) {
            return $html;
        }
        while (preg_match_all(
            '@\$translate\((.*?)\)@ms',
            $html,
            $matches
        )) {
            $ids = [];
            foreach ($matches[1] as $i => $match) {
                try {
                    $decoded = unserialize(base64_decode($match));
                    $matches[1][$i] = $decoded;
                } catch (ErrorException $e) {
                    $matches[1][$i] = [$match, null, []];
                }
                foreach ($matches[1][$i][0] as $j => $one) {
                    if (strpos($one, '\\') === false) {
                        $matches[1][$i][0][$j]
                            = $this->currentNamespace()."\\$one";
                    }
                }
                $ids[] = $matches[1][$i][0];
            }
            $this->text->load($matches[1]);
            foreach ($matches[1] as $i => $match) {
                $matches[1][$i] = call_user_func_array(
                   [$this->text, 'retrieve'],
                    $match
                );
            }
            $html = str_replace($matches[0], $matches[1], $html);
        }
        return $html;
    }
}

