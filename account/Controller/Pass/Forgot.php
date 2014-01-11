<?php

/**
 * @package monolyth
 * @subpackage account
 */

namespace monolyth\account;
use monolyth\Controller;
use monolyth\Nologin_Required;
use monolyth\HTTP301_Exception;

class Forgot_Pass_Controller extends Controller implements Nologin_Required
{
    public function __construct()
    {
        parent::__construct();
        $this->form = new Forgot_Pass_Form;
    }

    protected function get(array $args)
    {
        return $this->view('page/pass/forgot');
    }

    protected function post(array $args)
    {
        if (!$this->form->errors()) {
            $reset = new Reset_Pass_Model;
            if ($error = call_user_func($reset, $this->form)) {
                self::message()->add(
                    'error',
                    $this->text("pass/forgot/error.$error")
                );
            } else {
                self::message()->add(
                    'success',
                    $this->text(
                        'pass/forgot/success',
                        $this->form['email']->value
                    )
                );
                throw new HTTP301_Exception(
                    $this->url('monolyth/account/login')
                );
            }
        }
        return $this->get($args);
    }
}

