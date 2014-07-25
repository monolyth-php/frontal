<?php

namespace monolyth\api;
use monolyth\account;
use monolyth\HTTP301_Exception;
use monolyth\render\FileNotFound_Exception;
use ErrorException;

class Activate_Controller extends account\Do_Activate_Controller
{
    protected $template = false;

    protected function post(array $args)
    {
        try {
            parent::get(['id' => $_POST['id'], 'hash' => $_POST['hash']]);
        } catch (FileNotFound_Exception $e) {
            // That's fine, we don't need the entire page anyway here.
        } catch (HTTP301_Exception $e) {
            // This we definitely don't want for ajaxy API controllers.
        } catch (ErrorException $e) {
            // Prolly post was invalid.
        }
        return $this->view(
            'monolyth\render\json',
            ['data' => self::user()->active()]
        );
    }
}

