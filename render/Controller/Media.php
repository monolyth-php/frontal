<?php

namespace monolyth\render;
use monolyth\core\Controller;
use monolyth\HTTP404_Exception;
use monolyth\adapter\sql\NoResults_Exception;

class Media_Controller extends Controller
{
    protected $template = false;

    protected function get(array $args)
    {
        extract($args);
        switch ($ext) {
            case 'jpg': case 'jpeg': $mime = 'image/jpeg'; break;
            case 'png': case 'gif': $mime = "image/$ext"; break;
            default: throw new HTTP404_Exception;
        }
        $where = ['mimetype' => $mime];
        if (isset($owner)) {
            $where['owner'] = $owner;
        }
        if (isset($media)) {
            $where['id'] = $media;
        }
        if (isset($md5)) {
            $where['md5'] = $md5;
        }
        if (!($q = $this->medias->query($where))) {
            throw new HTTP404_Exception;
        }
        $fn = 'image';
        if ($ext == 'jpg') {
            $fn .= 'jpeg';
        } else {
            $fn .= $ext;
        }
        if ($q['data']) {
            $img = imagecreatefromstring($q['data']);
        } elseif ($q['filename']) {
            $img = call_user_func(
                'imagecreatefrom'.(end(explode('/', $q['mimetype']))),
                $q['filename']
            );
        } else {
            throw new HTTP404_Exception;
        }
        header("Content-type: {$q['mimetype']}");
        if (isset($size) && $size) {
            if (isset($this->config->size)) {
                if (!isset($this->config->size[$size])) {
                    throw new HTTP404_Exception();
                }
                $img = $this->box(
                    $img,
                    $this->config->size[$size][0],
                    $this->config->size[$size][1]
                );
            } else {
                $img = $this->box($img, $size, $size);
            }
        }
        $fn($img);
    }

    protected function box($img, $width, $height)
    {
        $i = [imagesx($img), imagesy($img)];
        // Push into a box of $width x $height pixels
        $mod = $i[0] / $i[1];
        if ($width / $height > $mod) {
            $nh = $width / $mod;
            $nw = $width;
        } else {
            $nw = $height * $orig;
            $nh = $height;
        }
        $mids = [round($nw / 2), round($nh / 2)];
        $tmp = imagecreatetruecolor(round($nw), round($nh));
        $white = imagecolorallocate($tmp, 255, 255, 255);
        imagefill($tmp, 0, 0, $white);
        imagecopyresampled($tmp, $img, 0, 0, 0, 0, $nw, $nh, $i[0], $i[1]);
        $new = imagecreatetruecolor($width, $height);
        imagecopyresampled(
            $new, $tmp, 0, 0, 
            $mids[0] - $width / 2, $mids[1] - $height / 2,
            $width, $height, $width, $height
        );
        imagedestroy($tmp);
        imagedestroy($img);
        return $new;
    }
}

