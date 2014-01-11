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

class Request_Activate_Controller extends Activate_Controller
{
    /**
     * Request an activation mail for the current user.
     */
    protected function get(array $args)
    {
        extract($args);
        $user = self::user();
        if (!($user->status() & $user::STATUS_INACTIVE)) {
            self::message()->add('info', $this->text('./noneed'));
            throw new HTTP301_Exception($this->url('monolyth/account'));
        }
        return $this->view('page/activate/request');
    }

    protected function post(array $args)
    {
        extract($args);
        if (!isset($id, $hash)) {
            if ($error = $this->activate->request(self::user()->id())) {
                return $this->view('page/activate/resendfailed');
            }
            self::message()->add(
                'success',
                $this->text('./success', self::user()->email())
            );
            return $this->view('page/activate/resent');
        }
    }
}

