<?php

/**
 * @package monolyth
 * @subpackage account
 */

namespace monolyth\account;
use monolyth\HTTP301_Exception;
use monolyth\Controller;
use monolyth\Login_Required;

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
        if (!($errors = $this->form->validate())) {
            /** All okay; update the password. */
            $pass = new Pass_Model;
            if (!($error = $pass->update($this->form['new']->value))) {
                self::message()->add(
                    'success',
                    $this->text('pass/update/success')
                );
                $url = $this->url('monolyth/account/update');
                throw new HTTP301_Exception($url);
            } else {
                self::message()->add(
                    'error',
                    "monolyth\\account\\pass/update/error.$error"
                );
            }
        } else {
            foreach ($errors as $error) {
                self::message()->add(
                    'error',
                    "monolyth\\account\\pass/update/error.$error"
                );
            }
        }
        return $this->get($args);
    }
}

