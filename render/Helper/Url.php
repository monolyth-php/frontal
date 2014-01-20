<?php

namespace monolyth\render;
use monolyth\Language_Access;

trait Url_Helper
{
    use Router_Access;
    use Language_Access;

    /**
     * Generate an URL based on $route, using $args arguments and/or context.
     *
     * @param mixed $route Object, classname or simplified route.
     * @param array $args Hash of arguments to pass to the URL.
     * @param bool $context Whether or not to include "context" (scheme/domain),
     *                      even if not necessary (e.g. on the same domain).
     *                      Defaults to false.
     * @return string The generated route (empty if not found).
     * @see monolyth\Adavanced_Router
     */
    public function url($route, array $args = [], $context = false)
    {
        if (!isset($args['language'])) {
            $args['language'] = self::language()->current->code;
        }
        if ($url = self::router()->generate($route, $args, $context)) {
            return $url;
        }
        unset($args['language']);
        return self::router()->generate($route, $args, $context);
    }

    /**
     * Change a random snippet of text into a URL-enabled slug, optionally
     * ensuring its uniqueness.
     *
     * @param string $text Some text. Remember not to make slugs too long; they
     *                     have a hard-coded limit of 255 characters.
     * @param callable $check A callable that checks for uniqueness.
     * @return string A slug.
     */
    public function slug($text, $check = null)
    {
        $text = htmlentities(strtolower(strip_tags($text)));
        $text = preg_replace('@&([a-z])[a-z]+?;@', '$1', $text);
        $text = preg_replace('@[^\w\s-]@', '', $text);
        $maxlength = 255;
        $text = substr(preg_replace('@\s+@', '-', trim($text)), 0, $maxlength);
        if (isset($check) && is_callable($check)) {
            $i = 1;
            while ($check($text)) {
                $text = preg_replace('@-\d+$@', '', $text);
                ++$i;
                $mylength = $maxlength - strlen($i) - 1;
                $text = substr($text, 0, $mylength)."-$i";
            }
        }
        return $text;
    }

    /**
     * Generates a hash of the specified number of characters. Only
     * characters that are safe in URLs are used.
     *
     * The idea here is that hashes generally result in looooong strings,
     * which are ugly and impractical in URLs. This helper function generates
     * a whirlpool hash of the input, base64_encodes the raw hash and then
     * removes characters until arriving at the requested length.
     *
     * Since a base64_encoded string has 63 different characters, this results
     * in (for the default length of 6) in well over 64 billion different
     * values.
     *
     * To keep the resulting URL "safe", the equals sign (=) and the slash
     * character in the resulting hash are stripped prior to "compression".
     *
     * If you need to salt the input, be sure to append it yourself before
     * passing it to this function.
     *
     * @param mixed $value The value to hash for. If this is not a scalar,
     *                     it is serialized first, so you can really pass
     *                     just about anything.
     * @param integer $characters The number of characters the returned hash
     *                            should use.
     * @return The URL-safe short hash.
     */
    public function hash($value, $characters = 6)
    {
        // Make sure $value is a scalar.
        if (!is_scalar($value)) {
            $value = serialize($value);
        }

        // Apply whirlpool hash to completely bonk it.
        // (Is bonk a word? It should be.)
        $return = hash('whirlpool', $value, true);

        // Base64_encode what's left.
        $return = base64_encode($return);
        // Strip equals and slash sign.
        $return = str_replace(['=', '/'], '', $return);

        // Incrementally "remove" information till we're left with
        // the desired amount of characters.
        $idx = 0;
        $start = 0;
        $str = '';
        do {
            $idx += $characters;
            if ($idx > strlen($return)) {
                $idx = ++$start;
            }
            $str .= substr($return, $idx, 1);
        } while (strlen($str) < $characters);
        return $str;
    }

    /**
     * Returns a target="_blank" attribute, but ONLY if the URL in question
     * leads to an external website.
     */
    public function blank($url)
    {
        $parts1 = explode('.', $url);
        while (count($parts1) > 2) {
            array_shift($parts1);
        }
        $parts2 = explode('.', $_SERVER['SERVER_NAME']);
        while (count($parts2) > 2) {
            array_shift($parts2);
        }
        return $parts1 != $parts2 ? ' target="_blank"' : '';
    }
}

