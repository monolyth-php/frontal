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
    protected function get(array $args)
    {
        return $this->view('page/pass/forgot');
    }

    protected function post(array $args)
    {
        if (!$this->form->errors()) {
            if ($error = call_user_func($this->reset, $this->form)) {
                $this->message->add(
                    self::MESSAGE_ERROR,
                    $this->text("pass/forgot/error.$error")
                );
            } else {
                $this->message->add(
                    self::MESSAGE_SUCCESS,
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

