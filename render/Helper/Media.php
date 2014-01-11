<?php

namespace monolyth\render;
use Adapter_Access;
use monolyth\Project_Access;
use monolyth\adapter\sql\NoResults_Exception;
use ErrorException;

class Media_Helper
{
    use Url_Helper;
    use Adapter_Access;
    use Project_Access;
    use Static_Helper;

    public function http($img, array $options = [])
    {
        $found = null;
        if (is_numeric($img)) {
            try {
                $img = self::adapter()->row(
                    'monolyth_media',
                    '*',
                    ['id' => $img]
                );
            } catch (NoResults_Exception $e) {
                return '';
            }
        }
        $parts = explode('/', $img['mimetype']);
        $ext = end($parts);
        if ($ext == 'jpeg') {
            $ext = 'jpg';
        }
        $tries = [];
        foreach ([
            ['media' => 'id', 'owner' => 'owner'],
            ['media' => 'id'],
            ['md5' => 'md5', 'owner' => 'owner'],
            ['md5' => 'md5'],
        ] as $maybe) {
            try {
                $subs = [];
                foreach ($maybe as $key => $map) {
                    $subs[$key] = $img[$map];
                }
            } catch (ErrorException $e) {
                continue;
            }
            $tries[] = $options + $subs;
        }
        foreach ($tries as $opts) {
            if (!isset($opts['ext'])) {
                $opts['ext'] = $ext;
            }
            if ($found = $this->url('monolyth/render/media', $opts)) {
                break;
            }
        }
        if (!isset($found)) {
            return '';
        }
        return $this->httpimg($found);
    }
}

