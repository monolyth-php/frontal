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
            self::message()->add(
                'success',
                $this->text('./success')
            );
            return $this->view('page/reactivate/success');
        }
        self::message()->add('error', $this->text("./error.$error"));
        throw new HTTP301_Exception($this->url(
            'monolyth/account/request_reactivate'
        ));
    }
}

