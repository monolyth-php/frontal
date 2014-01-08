<?php

/**
 * @package monolyth
 * @subpackage account
 */

namespace monolyth\account;
use monolyth\Controller;
use adapter\Access as Adapter_Access;
use monolyth\render\Url_Helper;
use monolyth\HTTP301_Exception;
use monolyth\Confirm_Model;
use monolyth\Message;

class Confirm_Pass_Controller extends Controller
{
    use Adapter_Access;

    public function __construct()
    {
        parent::__construct();
        $this->form = new Confirm_Pass_Form;
    }

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
            if (!($error = (new Confirm_Model)->process($hash, $id))) {
                $pw = self::adapter()->field(
                    'monolyth_auth',
                    'pass',
                    compact('id')
                );
                (new Pass_Model)->update($pw, $id);
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
        }
        $url = $this->url('monolyth/account/forgot_pass');
        throw new HTTP301_Exception($url);
    }
}

