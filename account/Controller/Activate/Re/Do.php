<?php

/**
 * @package monolyth
 * @subpackage account
 */

namespace monolyth\account;
use monolyth\HTTP301_Exception;

class Do_Re_Activate_Controller extends Activate_Controller
{
    /**
     * Activate a user. User should be logged in as the user whose
     * hash gets passed in the URI.
     */
    protected function get(array $args)
    {
        extract($args);
        $this->form->addSource(compact('id', 'hash'))->load();
        if (!($error = call_user_func($this->activate, $this->form))) {
            $this->message->add(
                self::MESSAGE_SUCCESS,
                $this->text('./success')
            );
            return $this->view('page/reactivate/success');
        }
        $this->message->add(self::MESSAGE_ERROR, $this->text("./error.$error"));
        throw new HTTP301_Exception($this->url(
            'monolyth/account/request_reactivate'
        ));
    }
}

