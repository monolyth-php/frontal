<?php

/**
 * @package monolyth
 * @subpackage account
 */

namespace monolyth\account;
use monolyth\Controller;
use monolyth\Logout_Required;
use monolyth\HTTP301_Exception;

class Login_Controller extends Controller implements Logout_Required
{
    protected function get(array $args)
    {
        if ($this->user->loggedIn()) {
            $this->user->logout();
        }
        return $this->view('page/login');
    }

    protected function post(array $args)
    {
        $view = $this->get($args);
        if (!($this->form->errors()
            or $error = $this->user->login($this->form)
        )) {
            $redir = urldecode($this->http->getRedir());
            if (is_null($redir)
                || $redir == $this->url(get_class($this))
                || $redir == $this->url(get_class($this), [], true)
                || $redir == $this->url(__CLASS__)
                || $redir == $this->url(__CLASS__, [], true)
            ) {
                $redir = $this->url('');
            }
            if (preg_match("@^\w+://@", $redir)
                && !preg_match('@^http://'.$this->http->server().'@', $redir)
            ) {
                $redir .= strpos($redir, '?') ? '&' : '?';
                $redir .= 'sid='.$this->session->id();
            }
            throw new HTTP301_Exception($redir);
        } else {
            if (isset($error)) {
                $this->message->add(
                    self::MESSAGE_ERROR,
                    $this->text("login/error.$error")
                );
            }
        }
        return $view;
    }
}

