<?php

namespace monolyth\api;
use monolyth\Controller;
use monolyth\HTTP404_Exception;
use monolyth\User_Access;

class Session_Controller extends Controller
{
    protected $template = false;

    protected function get(array $args)
    {
        $session = self::session()->all();
        if (isset($session['User'])) {
            foreach ([
                'pass', 'salt', 'ipcreated', 'ipmodified', 'ipactive',
            ] as $remove) {
                unset($session['User'][$remove]);
            }
        }
        if (isset($session['Groups'])) {
            unset($session['Groups']);
        }
        $session['_id'] = session_id();
        return $this->view('monolyth\render\json', ['data' => $session]);
    }

    protected function post(array $args)
    {
        self::session()->stop();
        return $this->view('monolyth\render\json', ['data' => 1]);
    }
}

