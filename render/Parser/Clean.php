<?php

namespace monolyth\render;
use monolyth\core\Parser;

class Clean_Parser extends Parser
{
    public function __invoke($html)
    {
        // Don't self-close void tags:
        $html = preg_replace(
            "@<(".implode('|', [
                'area',
                'base',
                'br',
                'col',
                'command',
                'embed',
                'hr',
                'img',
                'input',
                'keygen',
                'link',
                'meta',
                'param',
                'source',
                'track',
                'wbr'
            ]).")(.*?)\s*/>@ms",
            '<\\1\\2>',
            $html
        );

        // Remove trailing semicolons in inline styles:
        $html = preg_replace('@style="(.*?);"@ms', 'style="\\1"', $html);

        // For images without an alt-attribute, add an empty one:
        $html = preg_replace_callback(
            '@<img(.*?)>@ms',
            function($match) {
                if (strpos($match[1], 'alt="') === false) {
                    return str_replace(
                        $match[1],
                        "{$match[1]} alt=\"\"",
                        $match[0]
                    );
                }
                return $match[0];
            },
            $html
        );

        // Remove empty paragraphs. Empty elements in themselves are fine, but
        // empty paragraphs are typically the left-overs from (crappy or
        // otherwise careless) WYSIWYG editors:
        $html = preg_replace('@<p>\s*(&nbsp;)?\s*</p>@ms', '', $html);
        return $html;
    }
}

