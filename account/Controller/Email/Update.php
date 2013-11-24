<?php

/**
 * @package monolyth
 * @subpackage account
 */

namespace monolyth\account;
use monolyth\Controller;
use monolyth\Login_Required;

class Update_Email_Controller extends Controller implements Login_Required
{
    protected function get(array $args)
    {
        return $this->view('page/email/update');
    }

    protected function post(array $args)
    {
        if (!$this->form->errors()) {
            if (!($error = $this->account->email($this->form))) {
                $this->message->add(
                    self::MESSAGE_SUCCESS,
                    $this->text('./confirm')
                );
            } else {
                $this->message->add(
                    self::MESSAGE_ERROR,
                    $this->text("./error.$error")
                );
            }
        }
        return $this->get($args);
    }
}

