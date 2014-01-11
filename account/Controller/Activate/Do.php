<?php

/**
 * @package monolyth
 * @subpackage account
 */

namespace monolyth\account;
use monolyth\HTTP301_Exception;
use monolyth\Confirm_Form;

class Do_Activate_Controller extends Activate_Controller
{
    /**
     * Activate a user. User should be logged in as the user whose
     * hash gets passed in the URI.
     */
    protected function get(array $args)
    {
        extract($args);
        $form = new Confirm_Form;
        $form->addSource(compact('id', 'hash'))->load();
        $activate = new Activate_Model;
        if (!($error = call_user_func($activate, $form))) {
            self::message()->add('success', $this->text('./success'));
            return $this->view('page/activate/success');
        }
        self::message()->add('error', $this->text("./error.$error"));
        throw new HTTP301_Exception($this->url(
            'monolyth/account/request_activate'
        ));
    }
}

