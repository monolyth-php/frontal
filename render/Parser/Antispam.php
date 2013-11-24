<?php

namespace monolyth\render;
use monolyth\core\Parser;

class Antispam_Parser extends Parser
{
    public function __invoke($html)
    {
        $body = $this->body($html);
        if (preg_match_all(
            '@<a.*?href="(mailto:[^?].*?)".*?>(.*?)</a>@msi',
            $body,
            $matches
        )) {
            foreach ($matches[0] as $key => $complete) {
                $body = str_replace(
                    $complete,
                    str_replace(
                        [
                            $matches[1][$key],
                            $matches[2][$key]
                        ],
                        [
                            '#'.implode(',', [
                                base64_encode($matches[1][$key]),
                                base64_encode($matches[2][$key]),
                            ]).'" class="_email',
                            $this->text('./replaced')
                        ],
                        $complete
                    ),
                    $body
                );
            }
        }
        return $this->html($body);
    }
}

