<?php

namespace monolyth\account;
use monolyth\Login_Required;
use monolyth\HTTP301_Exception;
use monolyth\core\Staged_Controller;

class Delete_Controller extends Staged_Controller implements Login_Required
{
    protected static $stages = ['confirm', 'success'];

    protected function post(array $args)
    {
        if (!$this->form->errors()) {
            // Delete account:
            if ($error = $this->delete->delete()) {
                $this->message->add(
                    self::MESSAGE_ERROR,
                    $this->text("./error.$error")
                );
            }
        }
        return $this->get($args);
    }

    protected function cancel()
    {
        throw new HTTP301_Exception($this->url('monolyth/account'));
    }
}

