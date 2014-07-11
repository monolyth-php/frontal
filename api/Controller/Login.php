<?php

namespace monolyth\api;
use monolyth\account;
use monolyth\HTTP301_Exception;
use monolyth\HTTP404_Exception;
use monolyth\render\FileNotFound_Exception;
use monolyth\render\Ajax_Translate_Parser;

class Login_Controller extends account\Login_Controller
{
    protected $template = false;

    protected function post(array $args)
    {
        try {
            parent::post($args);
        } catch (FileNotFound_Exception $e) {
            // That's fine, we don't need the entire page anyway here.
        } catch (HTTP301_Exception $e) {
            // This we definitely don't want for ajaxy API controllers.
        }
        return $this->view(
            'monolyth\render\page/json',
            ['data' => self::user()->loggedIn()]
        );
    }
}

