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
    protected function get(array $args)
    {
        return $this->view('page/pass/update');
    }

    protected function post(array $args)
    {
        if (!$this->form->errors()) {
            /** All okay; update the password. */
            if (!($error = $this->pass->update($this->form['new']->value))) {
                $this->message->add(
                    self::MESSAGE_SUCCESS,
                    $this->text('pass/update/success')
                );
                $url = $this->url('monolyth/account/update');
                throw new HTTP301_Exception($url);
            }
        }
        return $this->get($args);
    }
}

