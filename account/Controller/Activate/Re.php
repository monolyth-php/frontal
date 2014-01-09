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
use monolyth\db;

class Re_Activate_Controller extends Controller implements Login_Required
{
    /**
     * Re-activate a user. User should be logged in as the user whose
     * hash gets passed in the URI. If $hash isn't given, a new activation
     * mail is sent out instead.
     */
    protected function get(array $args)
    {
        extract($args);
        $user = self::user();
        if (!($user->status() & $user::STATUS_EMAIL_UNCONFIRMED)) {
            self::message()->add(Message::INFO, $this->text('./noneed'));
            throw new HTTP301_Exception($this->url('monolyth/account'));
        }

        if (!($error = call_user_func($this->activate, $this->form))) {
            return $this->view('page/activate/re/success');
        }
        if ($error == 'noneed') {
            return $this->view('page/activate/re/noneed');
        }
        return $this->view('page/activate/re/error');
    }

    protected function post(array $args)
    {
        extract($args);
        if (!isset($id, $hash)) {
            if ($error = (new Activate_Model)->request(self::user()->id())) {
                return $this->view('page/activate/re/resendfailed');
            }
            return $this->view('page/activate/re/resent');
        }
        return $this->get($args);
    }
}

