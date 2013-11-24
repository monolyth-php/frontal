<?php

/**
 * @package monolyth
 * @subpackage render
 */

namespace monolyth\render;
/**
 *
 */
trait Text_Helper
{
    public function excerpt($text, $chars = 75, $lines = 1)
    {
        $tmp = substr(
            trim(str_replace("\n", ' ', $text)),
            0,
            $chars * $lines
        );
        $tmp = explode("\n", wordwrap($tmp, $chars), $lines + 1);
        if (count($tmp) == $lines + 1) {
            array_pop($tmp);
        }
        return implode("\n", $tmp);
    }

    public function paragraphs($text)
    {
        $text = str_replace(
            '<p></p>',
            '',
            '<p>'.preg_replace(
                '@(<br.*?>\s*?){2,}@m',
                "</p>\n\n<p>",
                nl2br($text)
            )."</p>\n"
        );
        return preg_replace('@<br.*?>@m', '<br>', $text);
    }
}

