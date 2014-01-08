<?php

/**
 * @package monolyth
 * @subpackage account
 */

namespace monolyth\account;
use monolyth\HTTP301_Exception;
use monolyth\Controller;
use monolyth\adapter;
use monolyth\render\Url_Helper;
use monolyth\Nologin_Required;
use monolyth\Confirm_Model;
use monolyth\Message;

class Reset_Pass_Controller extends Controller
implements adapter\Access, Nologin_Required
{
    public function __construct()
    {
        parent::__construct();
        $this->form = new Reset_Pass_Form;
        $this->confirm = new Confirm_Model;
        $this->pass = new Pass_Model;
    }

    protected function get(array $args)
    {
        extract($args);
        $this->form->addSource(compact('id', 'hash'))->load();
        if (!($error = $this->confirm->process($hash, $id))) {
            $pw = self::adapter()->field(
                'monolyth_auth',
                'pass',
                compact('id')
            );
            $this->pass->update($pw, $id);
            self::message()->add(
                Message::SUCCESS,
                $this->text('pass/reset/success', ['pass' => $pw])
            );
            return $this->view('page/pass/display', ['pass' => $pw]);
        } elseif ($error == 'contains outdated elements') {
            self::message()->add(
                Message::ERROR,
                $this->text('pass/reset/error.date')
            );
        } else {
            self::message()->add(
                Message::ERROR,
                $this->text('pass/reset/error.generic')
            );
        }
        $url = $this->url('monolyth/account/forgot_pass');
        throw new HTTP301_Exception($url);
    }
}

