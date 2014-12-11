<?php

namespace monolyth\api;
use monolyth\account;
use monolyth\HTTP301_Exception;
use monolyth\HTTP405_Exception;
use monolyth\HTTP500_Exception;

class Reset_Pass_Controller extends account\Forgot_Pass_Controller
{
    protected $template = false;

    public function __construct()
    {
        parent::__construct();
        if (isset($_POST['id'], $_POST['code'])) {
            $this->form = new account\Reset_Pass_Form;
        }
    }

    protected function get(array $args)
    {
        throw new HTTP405_Exception;
    }

    protected function post(array $args)
    {
        if (!$this->form->errors()) {
            $reset = new Reset_Pass_Model;
            if (isset($_POST['email'])) {
                if ($error = call_user_func($reset, $this->form)) {
                    self::message()->add('error', "pass/forgot/error.$error");
                } else {
                    self::message()->add('success', 'pass/forgot/success');
                }
                return $this->view(
                    'monolyth\render\json',
                    ['data' => [
                        'code' => substr($reset['code'], 0, 3),
                        'id' => $reset['id'],
                    ]]
                );
            } elseif (isset($_POST['id'], $_POST['code'])) {
                if ($error = $reset->process($_POST['id'], $_POST['code'])) {
                    self::message()->add(
                        'error',
                        "pass/confirm/error.$error"
                    );
                    return $this->view('monolyth\render\json', ['data' => 0]);
                } else {
                    self::message()->add('success', 'pass/confirm/success');
                    return $this->view('monolyth\render\json', ['data' => 1]);
                }
            }
        }
        throw new HTTP500_Exception;
    }
}

