<?php

/**
 * @package monolyth
 * @subpackage account
 */

namespace monolyth\account;
use monolyth\User;
use monolyth\model\Auth;
use monolyth\render\Url_Helper;
use monolyth\HTTP301_Exception;
use monolyth\db;

class Request_Activate_Controller extends Activate_Controller
{
    /**
     * Request an activation mail for the current user.
     */
    protected function get(array $args)
    {
        extract($args);
        $user = $this->user;
        if (!($user->status() & $user::STATUS_INACTIVE)) {
            $this->message->add(self::MESSAGE_INFO, $this->text('./noneed'));
            throw new HTTP301_Exception($this->url('monolyth/account'));
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

