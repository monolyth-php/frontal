<?php

/**
 * @package monolyth
 * @subpackage api
 */

namespace monolyth\api;
use monolyth\Controller;
use monolyth\Ajax_Login_Required;
use monolyth\account\Pass_Model;
use monolyth\account\Update_Pass_Form;

class Update_Pass_Controller extends Controller implements Ajax_Login_Required
{
    protected $template = false;

    public function __construct()
    {
        parent::__construct();
        $this->form = new Update_Pass_Form;
    }

    protected function post(array $args)
    {
        if (!$this->form->errors()) {
            /** All okay; update the password. */
            $pass = new Pass_Model;
            if (!($error = $pass->update($this->form['new']->value))) {
                self::message()->add(
                    'success',
                    'monolyth\account\pass/update/success'
                );
                return $this->view('monolyth\render\json', ['data' => 1]);
            }
        }
        return $this->view('monolyth\render\json', ['data' => 0]);
    }
}

