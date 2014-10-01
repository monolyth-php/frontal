<?php

/**
 * @package monolyth
 * @subpackage api
 */

namespace monolyth\api;
use monolyth\Controller;
use monolyth\Ajax_Login_Required;
use monolyth\account\Update_Email_Form;
use monolyth\account\User_Model;
use monolyth\HTTP500_Exception;

class Update_Email_Controller extends Controller implements Ajax_Login_Required
{
    public function __construct()
    {
        parent::__construct();
        $this->form = new Update_Email_Form;
    }

    protected function post(array $args)
    {
        die('ok');
        if (!$this->form->errors()) {
            $account = new User_Model;
            if (!($error = $account->email($this->form))) {
                self::message()->add(
                    'success',
                    'monolyth\account\email/update/confirm'
                );
                return $this->view('monolyth\render\json', ['data' => 1]);
            } else {
                self::message()->add(
                    'error',
                    "monolyth\\account\\email/update/error.$error"
                );
                return $this->view('monolyth\render\json', ['data' => 0]);
            }
        }
        throw new HTTP500_Exception;
    }
}

