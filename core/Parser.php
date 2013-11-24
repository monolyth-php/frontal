<?php

/**
 * The base Parser-class.
 *
 * Parsers are intended to modify chunks of HTML. Examples of stuff you'd
 * want to handle via a parser are:
 * - substitute URLs or e-mail addresses with the correct anchors
 * - validate HTML
 * - insert <abbr> or <acronym> tags where appropriate
 * - highlight certain keywords
 * The distinction between using a Parser and simply writing out correct HTML
 * is admittedly a rather thin and academic one. Parsers are utilities; they're
 * meant to make your life easier. If you want to <abbr> every instance of
 * 'HTML' in your site, it would be a drag to it by hand if you wrote about
 * HTML for instance. Also, you might want to have mailto links on e-mail
 * addresses for logged-in users, but not for anonymous ones. Writing this
 * out in full every time can become quite tedious, and Parsers save you.
 *
 * @package monolyth
 * @subpackage core
 * @author Marijn Ophorst <marijn@monomelodies.nl>
 * @copyright MonoMelodies 2008, 2009, 2010, 2011, 2012
 */

namespace monolyth\core;
use monolyth\utils\Translatable;

abstract class Parser
{
    use Translatable;

    private $html = [];
    private $namespace;

    const KEEP_ORIGINAL = 1;
    const KEEP_NEW = 2;

    /**
     * Do the actual parsing.
     *
     * @param string $html The HTML to parse.
     * @param mixed $arg,... Additional arguments to use when parsing.
     */
    public function __invoke($html)
    {
        return $html;
    }

    /**
     * Set or get the namespace of the controller invoking this Parser.
     *
     * @param string $namespace The namespace to set.
     * @return string The current namespace.
     */
    public function currentNamespace($namespace = null)
    {
        if (isset($namespace)) {
            $this->namespace = $namespace;
        }
        return $this->namespace;
    }

    /**
     * Return the body-part of the HTML (the head generally won't need parsing).
     *
     * @param string $html A blob of HTML.
     * @return string The part of $html that contains the body.
     */
    protected function body($html)
    {
        if (preg_match('@(<body.*?>)(.*?)</body>@ms', $html, $match)) {
            $this->html = [$html, $match[0]];
            return $match[0];
        } else {
            $this->html = [$html, $html];
            return $html;
        }
    }

    /**
     * Return the textnodes for $body, since you generally don't want to parse
     * HTML inside HTML tags.
     *
     * @param string $body A blob of HTML.
     * @return array An array of textnodes alternated with HTML nodes.
     */
    public function textnodes($body)
    {
        return preg_split("@(<.*?>)@ms", $body, -1, PREG_SPLIT_DELIM_CAPTURE);
    }

    /**
     * Re-insert the parsed HTML into the complete HTML with head etc.
     *
     * @param string $body A blob of HTML.
     * @retrun string A string with head etc. re-attached.
     */
    protected function html($body)
    {
        return str_replace($this->html[1], $body, $this->html[0]);
    }

    /**
     * Remove doubly nested tags, e.g. <a><a>...</a></a>.
     * This COULD already be checked during parsing, but that's slooooooow.
     *
     * @param string $body A blob of HTML.
     * @param string $tag The tag to check for doubleness.
     * @param int $type How to deal with double tags. Allowed values are
     *                  self::KEEP_ORIGINAL (keep the tag that was there
     *                  before parsing, also the default) and
     *                  self::KEEP_NEW (keep the tag inserted by the parser).
     */
    protected function removeDoubleTags($body, $tag, $type = null)
    {
        if (!isset($type)) {
            $type = self::KEEP_ORIGINAL;
        }
        $match = ($type == self::KEEP_ORIGINAL) ?
            "@(<$tag.*?>)<$tag.*?>(.*?</$tag>)</$tag>@im" :
            "@<$tag.*?>(<$tag.*?>)(.*?</$tag>)</$tag>@im";
        return preg_replace($match, '$1$2', $body);
    }
}

