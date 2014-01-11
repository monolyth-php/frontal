<?php

/**
 * @package monolyth
 * @subpackage account
 *
 * A login controller allowing the user to login via the external Twitter
 * service.
 */

namespace monolyth\account;
use monolyth\Controller;
use monolyth\Logout_Required;
use monolyth\User_Access;
use monolyth\HTTP301_Exception;

class Twitter_Login_Controller extends Controller implements Logout_Required
{
    use User_Access;

    public function __construct()
    {
        parent::__construct();
        $this->form = new Login_Form;
    }

    protected function get(array $args)
    {
        if (self::user()->loggedIn()) {
            self::user()->logout();
        }
        return $this->view('page/login');
    }

    protected function post(array $args)
    {
        $view = $this->get($args);
        if (!($this->form->errors()
            or $error = self::user()->login($this->form)
        )) {
            $redir = urldecode(self::http()->getRedir());
            if ($redir == $this->url('monolyth/account/login')
                || $redir == $this->url('monolyth/account/login', [], true)
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
                    $this->text("login/error.$error")
                );
            }
        }
        return $view;
    }
}

