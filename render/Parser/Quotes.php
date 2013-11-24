<?php

namespace monolyth\render;
use monolyth\core\Parser;

class Quotes_Parser extends Parser
{
    public function __invoke($html)
    {
        $body = $this->body($html);
        $map = ["'" => '&#8216;', '"' => '&#8220;'];
        $body = preg_replace(
            "@(\s)([\"'])(\w)@mse",
            '"$1".$map["$2"]."$3"',
            $body
        );
        $map = ["'" => '&#8217;', '"' => '&#8221;'];
        $body = preg_replace(
            "@(\w)([\"'])(\s)@mse",
            '"$1".$map["$2"]."$3"',
            $body
        );
        return $this->html($body);
    }
}

