<?php

/**
 * @package monolyth
 * @subpackage account
 */

namespace monolyth\account;
use monolyth\Controller;
use monolyth\Login_Required;
use monolyth\User;
use monolyth\model\Auth;
use monolyth\render\Url_Helper;
use monolyth\HTTP301_Exception;

class Request_Re_Activate_Controller extends Controller implements Login_Required
{
    /**
     * Activate a user. User should be logged in as the user whose
     * hash gets passed in the URI. If $hash isn't given, a new activation
     * mail is sent out instead.
     */
    protected function get(array $args)
    {
        extract($args);
        $user = $this->user;
        if (!($user->status() & $user::STATUS_INACTIVE)) {
            $this->message->add(self::MESSAGE_INFO, $this->text('./noneed'));
            throw new HTTP301_Exception($this->url('monolyth/account'));
        }
        if (isset($id, $hash)) {
            $this->form['id']->value = $id;
            $this->form['hash']->value = $hash;
        }
        if (!($error = call_user_func($this->activate, $this->form))) {
            return $this->view('page/activate/success');
        }
        if ($error == 'noneed') {
            return $this->view('page/activate/noneed');
        }
        return $this->view('page/activate/request');
    }

    protected function post(array $args)
    {
        extract($args);
        if (!isset($id, $hash)) {
            if ($error = $this->activate->request($this->user->id())) {
                return $this->view('page/activate/resendfailed');
            }
            $this->message->add(
                self::MESSAGE_SUCCESS,
                $this->text('./success', $this->user->email())
            );
            return $this->view('page/activate/resent');
        }
    }
}

