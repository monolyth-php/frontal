<?php

namespace monolyth\render;

trait Script_Parser
{
    public function parse($file, $data)
    {
        return preg_replace_callback(
            '@Monolyth\.include\((.*?)\);@ms',
            function($match) {
                $files = preg_split('@,\s+@ms', trim($match[1]));
                $files = array_map(function($element) {
                    return substr($element, 1, -1);
                }, $files);
                $return = '';
                foreach ($files as $file) {
                    $return .= file_get_contents($file, true);
                }
                return $return;
            },
            $data
        );
    }
}

