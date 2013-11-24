<?php

/**
 * @package monolyth
 * @subpackage account
 */

namespace monolyth\account;
use monolyth\Controller;
use monolyth\adapter;
use monolyth\render\Url_Helper;
use monolyth\HTTP301_Exception;

class Confirm_Pass_Controller extends Controller implements adapter\Access
{
    protected function get(array $args)
    {
        extract($args);
        $this->form->addSource(compact('id', 'hash'))->load();
        return $this->view('page/pass/confirm');
    }

    protected function post(array $args)
    {
        $view = $this->get($args);
        if (!$this->form->errors()) {
            extract($args);
            if (!($error = $this->confirm->process($hash, $id))) {
                $pw = $this->adapter->field(
                    'monolyth_auth',
                    'pass',
                    compact('id')
                );
                $this->pass->update($pw, $id);
                $this->message->add(
                    self::MESSAGE_SUCCESS,
                    $this->text('pass/reset/success', ['pass' => $pw])
                );
                return $this->view('page/pass/display', ['pass' => $pw]);
            } elseif ($error == 'contains outdated elements') {
                $this->message->add(
                    self::MESSAGE_ERROR,
                    $this->text('pass/reset/error.date')
                );
            } else {
                $this->message->add(
                    self::MESSAGE_ERROR,
                    $this->text('pass/reset/error.generic')
                );
            }
        }
        $url = $this->url('monolyth/account/forgot_pass');
        throw new HTTP301_Exception($url);
    }
}

