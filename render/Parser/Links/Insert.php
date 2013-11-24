<?php

/**
 * A default parser to replace href="(url_id[:[key1:value1,key2:value2,...]])"
 * with the correct URI.
 *
 * @package monolyth
 * @subpackage render
 * @author Marijn Ophorst <marijn@monomelodies.nl>
 * @copyright MonoMelodies 2008, 2009, 2010, 2012, 2013
 */

namespace monolyth\render;
use monolyth\core\Parser;
use ErrorException;

class Insert_Links_Parser extends Parser
{
    use Url_Helper;

    public function __invoke($html)
    {
        $body = $this->body($html);
        if (!preg_match_all(
            '@
                # within links
                href="
                # enclosed in parantheses
                (?:\(
                    # match controller name
                    ([a-z/_]+)?
                    # optionally followed by 1 or more arguments
                    (?::\[(.*?)\])?
                )\)
                # optionally follwed by fragment or query string
                ([#?].*?)?"
            @msxi',
            $body,
            $matches,
            PREG_SET_ORDER
        )) {
            return $this->html($body);
        }
        foreach ($matches as $match) {
            $args = [];
            if (isset($match[2]) && $match[2]) {
                foreach (explode(',', $match[2]) as $arg) {
                    try {
                        list($key, $value) = explode(':', $arg, 2);
                        $args[$key] = $value;
                    } catch (ErrorException $e) {
                    }
                }
            }
            $url = $this->url($match[1], $args);
            $body = str_replace(
                $match[0],
                sprintf('href="%s%s"', $url, isset($match[3]) ? $match[3] : ''),
                $body
            );
        }
        return $this->html($body);
    }
}

