<?php

namespace monolyth\render;

trait Extract_Media
{
    public function all($html, &$medias = [])
    {
        return preg_replace_callback(
            sprintf(
                '@src="(https?://%s)?/monad/media/(\d+)\.(jpe?g|png)"@ms',
                $_SERVER['SERVER_NAME']
            ),
            function($match) use(&$medias) {
                $medias[] = $match[2];
                return sprintf('src="{media:%d:%s}"', $match[2], $match[3]);
            },
            $html
        );
    }
}

