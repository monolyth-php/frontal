<?php

namespace monolyth\render;
use monolyth\core\Parser;
use Adapter_Access;
use monolyth\adapter\sql\NoResults_Exception;
use monolyth\Media_Model;

class Media_Parser extends Parser
{
    use Adapter_Access;

    public function __invoke($html)
    {
        if (!preg_match_all(
            "@
                (<|&lt;)img.*?src=(\"|&quot;)
                    {media:(\d+|[0-9a-f]{32}):(jpe?g|png|gif)}
                (\"|&quot;).*?(>|&gt;)
            @mx",
            $html,
            $matches,
            PREG_SET_ORDER
        )) {
            return $html;
        }
        $ids = [];
        foreach ($matches as $match) {
            $ids[] = $match[3];
        }
        try {
            $imgs = [];
            foreach (self::adapter()->rows(
                'monolyth_media',
                '*',
                [['id' => ['IN' => $ids], 'md5' => ['IN' => $ids]]]
            ) as $row) {
                $imgs[$row['id']] = $imgs[$row['md5']] = $row;
            }
        } catch (NoResults_Exception $e) {
            // None found; just strip everything.
            $replace = [];
            foreach ($matches as $match) {
                $replace[] = $match[0];
            }
            return str_replace($replace, '', $html);
        }
        $new = $old = [];
        $media = new Media_Model;
        foreach ($matches as $match) {
            $old[] = $match[0];
            if (!isset($imgs[$match[3]])) {
                $new[] = '';
                continue;
            }
            $ext = $match[4];
            if ($ext == 'jpeg') {
                $ext = 'jpg';
            }
            $width = $height = 0;
            if ($sizes = preg_match_all(
                '@(width|height)=("|&quot;)(\d+)("|&quot;)@m',
                $match[0],
                $m
            )) {
                foreach ($m[1] as $i => $ma) {
                    $$ma = $m[3][$i];
                }
            } elseif (preg_match(
                    '@style="(.*?)"@',
                    $match[0],
                    $tmp
                )
                && preg_match_all(
                    '@(width|height):\s+(\d+)px@m',
                    $tmp[1],
                    $m
                )
            ) {
                foreach ($m[1] as $i => $ma) {
                    $$ma = $m[2][$i];
                }
            }
            $new[] = str_replace(
                "src={$match[2]}{media:{$match[3]}:{$match[4]}}{$match[2]}",
                sprintf(
                    'src="%s"',
                    $media->media->http(
                        $imgs[$match[3]],
                        compact('width', 'height')
                    )
                ),
                $match[0]
            );
        }
        return str_replace($old, $new, $html);
    }
}

