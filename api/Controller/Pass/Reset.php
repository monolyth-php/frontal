<?php

namespace monolyth\api;
use monolyth\account;
use monolyth\HTTP301_Exception;
use monolyth\HTTP405_Exception;

class Reset_Pass_Controller extends account\Forgot_Pass_Controller
{
    protected $template = false;

    protected function get(array $args)
    {
        throw new HTTP405_Exception;
    }

    protected function post(array $args)
    {
        if (!$this->form->errors()) {
            $reset = new Reset_Pass_Model;
            if ($error = call_user_func($reset, $this->form)) {
                self::message()->add(
                    'error',
                    "pass/forgot/error.$error"
                );
            } else {
                self::message()->add(
                    'success',
                    'pass/forgot/success'
                );
            }
        }
        return $this->view('monolyth\render\json', ['data' => [
            'code' => substr($reset['code'], 0, 3)
        ]]);
    }
}

