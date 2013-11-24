<?php

namespace monolyth\render;

trait Css_Parser
{
    protected function parse($file, $data)
    {
        return preg_replace_callback(
            '@url\(([^/].*?)\)@m',
            function($match) use($file) {
                // Keep absolute URIs as-is.
                if (preg_match('@^([a-z]+:)?//@', $match[1])
                    || $match[1]{0} == '/'
                    || substr($match[1], 0, 2) == '"/'
                ) {
                    return $match[0];
                }
                $match[1] = preg_replace(
                    "@^(['\"])(.*?)\\1$@",
                    "\\2",
                    $match[1]
                );
                $prefix = '';
                if (substr($file, 0, 7) != 'output/') {
                    // This is an external stylesheet (most likely
                    // from a module) so we should reset the path.
                    $parts = explode('/', $file);
                    $prefix = '/'.array_shift($parts);
                    // Remove output/css, it's just fluff.
                    array_shift($parts);
                    array_shift($parts);
                    // Remove actual filename.
                    array_pop($parts);
                    $match[1] = implode('/', $parts)."/{$match[1]}";
                }
                return sprintf(
                    'url("%s")',
                    preg_replace(
                        "@(\w)//@",
                        '\\1/',
                        $this->httpimg("$prefix/css/{$match[1]}")
                    )
                );
            },
            $data
        );
    }
}

