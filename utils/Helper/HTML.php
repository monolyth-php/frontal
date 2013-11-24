<?php

/**
 * @package monolyth
 * @subpackage utils
 */

namespace monolyth\utils;
use HTMLPurifier;
use HTMLPurifier_Config;

trait HTML_Helper
{
    public function purify($html, $allowed = null)
    {
        // Clean obviously invalid stuff some editors might leave.
        $html = str_replace(
            ['<p><p>', '</p></p>'],
            ['<p>', '</p>'],
            $html
        );
        $html = trim(preg_replace(
            "@<(p|div)>(\s|&nbsp;)+?</(p|div)>@ms",
            '',
            $html
        ));
        $html = str_replace('<p></p>', '', $html);

        // Autoloading won't work for HTMLPurifier.
        require_once 'HTMLPurifier.auto.php';
        require_once 'monolyth/3rdparty/HTMLPurifier/Filter/Flash.php';
        $config = HTMLPurifier_Config::createDefault();
        $config->set('Filter.YouTube', true);

        // If $allowed wasn't specifically set, we'll just assume
        // ALL HTML is allowed in the value (what else can we do?).
        if (isset($allowed)) {
            $config->set('HTML.Allowed', $allowed);
        }
        $config->set('AutoFormat.AutoParagraph', true);
        $config->set('AutoFormat.RemoveEmpty', true);
        $config->set('AutoFormat.RemoveEmpty.RemoveNbsp', true);
        $purifier = new HTMLPurifier($config);
        return $purifier->purify($html);
    }

    public function stripSmart($string)
    {
        // Replace <br/> (in any variant) with newline.
        $string = preg_replace('@<br\s+?/?>@i', "\n", $string);

        // Replace paragraph elements with newlines.
        $string = preg_replace(
            '@<[Pp].*?>(.*?)</[Pp]>@ms',
            "\n$1\n",
            $string
        );
        // ...but let's not overdo it...
        $string = str_replace("\n\n\n", "\n\n", $string);

        // Replace <del> tags with \\1^W.
        $string = preg_replace_callback(
            '@<del.*?>(.*?)</del>@msi',
            function($match) {
                return preg_replace("@\s+@ms", "^W", $match[1]);
            },
            $string
        );

        // Replace anchors with something intelligent. For the best result
        // you'll probably want to manually load a text/plain template, but at
        // least this is better than nothing.
        $replace = [];
        if (preg_match_all(
            '@<a.*?href="(.*?)">(.*?)</a>(.*?)[!.?]@msi',
            $string,
            $matches
        )) {
            foreach ($matches[0] as $i => $match) {
                $replace[$match] = $matches[2][$i] // Part between <a> tags.
                                  .$matches[3][$i] // Part after up to end of
                                                   // sentence.
                                  .":\n{$matches[1][$i]}\n";
                                                   // URI on separate line.
            }
        }
        // Replace leftover anchors.
        $string = preg_replace_callback(
            '@<a.*?href="(.*?)">(.*?)</a>@msi',
            function($match) {
                return "{$match[1]}\n{$match[2]}";
            },
            $string
        );

        // Replace <b> or <strong> with *..*.
        $string = preg_replace(
            '@<(b|strong).*?>(.*?)</\1>@msi',
            "*\\2*",
            $string
        );
        // Replace <i> or <em> with /../.
        $string = preg_replace(
            '@<(i|em).*?>(.*?)</\1>@msi',
            "/\\2/",
            $string
        );

        return html_entity_decode(
            strip_tags($string),
            ENT_QUOTES,
            'UTF-8'
        );
    }
}

