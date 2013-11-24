<?php

namespace monolyth\render;
use monolyth\Controller;
use monolyth\adapter;
use monolyth\adapter\sql\NoResults_Exception;
use ErrorException;

class Edit_Media_Controller extends Controller implements adapter\Access
{
    protected function get(array $args)
    {
        extract($args);
        $this->template = $this->view('monolyth\template/page');
        $imagefile = null;
        if (isset($args['imagefile'])) {
            $imagefile = $args['imagefile'];
        } elseif ($id) {
            try {
                $imagefile = $this->adapter->field(
                    'monolyth_media',
                    'filename',
                    compact('id')
                );
            } catch (NoResults_Exception $e) {
            }
        }
        $max = strtolower(ini_get('upload_max_filesize'));
        switch (substr($max, -1)) {
            case 'm': $max = (int)$max * 1024 * 1024; break;
            case 'k': $max = (int)$max * 1024; break;
            default: $max = (int)$max;
        }
        $max = min($max, $this->config->upload_max_filesize);
        $error = null;
        $args += compact('imagefile', 'max', 'error');
        try {
            $args += ['position' => $_GET['i']];
        } catch (ErrorException $e) {
            $args += ['position' => 1];
        }
        unset($args['language']);
        return $this->view('page/media/edit', $args);
    }

    protected function post(array $args)
    {
        set_time_limit(0);
        $file = $error = null;
        if (isset($_FILES['media'])) {
            $error = $_FILES['media']['error'];
            $file = $this->config->private_tmp_path.
                '/'.md5(time().rand(0, 9999));
            $Media = $this->session->get('Media');
            if (!$Media) {
                $Media = [];
            }
            $Media[$file] = $_FILES['media']['name'];
            $this->session->set(compact('Media'));
            move_uploaded_file($_FILES['media']['tmp_name'], $file);
        }
        $args['imagefile'] = $file;
        $args += compact('error');
        return $this->get($args);
    }
}

