<?php

/**
 * @package monolyth
 * @subpackage account
 */

namespace monolyth\account;
use monolyth\HTTP301_Exception;
use monolyth\Controller;
use monolyth\Login_Required;
use monolyth\Message;

class Update_Pass_Controller extends Controller implements Login_Required
{
    public function __construct()
    {
        parent::__construct();
        $this->form = new Update_Pass_Form;
    }

    protected function get(array $args)
    {
        return $this->view('page/pass/update');
    }

    protected function post(array $args)
    {
        if (!$this->form->errors()) {
            /** All okay; update the password. */
            $pass = new Pass_Model;
            if (!($error = $pass->update($this->form['new']->value))) {
                self::message()->add(
                    Message::SUCCESS,
                    $this->text('pass/update/success')
                );
                $url = $this->url('monolyth/account/update');
                throw new HTTP301_Exception($url);
            }
        }
        return $this->get($args);
    }
}

