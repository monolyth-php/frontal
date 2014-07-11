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
    public function __construct()
    {
        parent::__construct();
        if (self::user()->loggedIn()) {
            self::user()->logout();
        }
        $this->form = new Login_Form;
    }

    protected function get(array $args)
    {
        return $this->view('page/login');
    }

    protected function post(array $args)
    {
        if (!($this->form->errors()
            or $error = self::user()->login($this->form)
        )) {
            $redir = urldecode(self::http()->getRedir());
            if (is_null($redir)
                || $redir == $this->url(get_class($this))
                || $redir == $this->url(get_class($this), [], true)
                || $redir == $this->url(__CLASS__)
                || $redir == $this->url(__CLASS__, [], true)
            ) {
                $redir = $this->url('');
            }
            if (preg_match("@^\w+://@", $redir)
                && !preg_match('@^http://'.self::http()->server().'@', $redir)
            ) {
                $redir .= strpos($redir, '?') ? '&' : '?';
                $redir .= 'sid='.self::session()->id();
            }
            throw new HTTP301_Exception($redir);
        } else {
            if (isset($error)) {
                self::message()->add(
                    'error',
                    __NAMESPACE__."\\login/error.$error"
                );
            }
        }
        return $this->get($args);
    }
}

