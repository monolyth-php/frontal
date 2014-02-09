<?php

/**
 * @package monolyth
 * @subpackage account
 */

namespace monolyth\account;
use monolyth\Controller;
use monolyth\Login_Required;
use monolyth\render\Url_Helper;
use monolyth\HTTP301_Exception;

class Request_Re_Activate_Controller extends Controller implements Login_Required
{
    public function __construct()
    {
        parent::__construct();
        $this->form = new Activate_Form;
    }

    /**
     * Activate a user. User should be logged in as the user whose
     * hash gets passed in the URI. If $hash isn't given, a new activation
     * mail is sent out instead.
     */
    protected function get(array $args)
    {
        extract($args);
        $user = self::user();
        if (!($user->status() & $user::STATUS_INACTIVE)) {
            self::message()->add('info', $this->text('./noneed'));
            throw new HTTP301_Exception($this->url('monolyth/account'));
        }
        if (isset($id, $hash)) {
            $this->form['id']->value = $id;
            $this->form['hash']->value = $hash;
        }
        if (!($error = call_user_func(new Activate_Model, $this->form))) {
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
            if ($error = (new Activate_Model)->request(self::user()->id())) {
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

