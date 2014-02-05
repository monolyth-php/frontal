<?php

/**
 * @package monolyth
 * @subpackage account
 */

namespace monolyth\account;
use monolyth\Controller;
use Adapter_Access;
use monolyth\render\Url_Helper;
use monolyth\HTTP301_Exception;
use monolyth\Confirm_Model;

class Confirm_Pass_Controller extends Controller
{
    use Adapter_Access;

    public function __construct()
    {
        parent::__construct();
        $this->form = new Reset_Pass_Form;
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
                extract(self::adapter()->row(
                    'monolyth_auth',
                    ['name', 'pass'],
                    compact('id')
                ));
                (new Pass_Model)->update($pass, $id);
                $form = new Login_Form;
                $form['name']->value = $name;
                $form['pass']->value = $pass;
                (new Login_Model)->__invoke($form);
                self::message()->add(
                    'success',
                    $this->text('pass/reset/success')
                );
                throw new HTTP301_Exception($this->url(
                    'monolyth/account/new_pass'
                ));
            } elseif ($error == 'contains outdated elements') {
                self::message()->add(
                    'error',
                    $this->text('pass/reset/error.date')
                );
            } else {
                self::message()->add(
                    'error',
                    $this->text('pass/reset/error.generic')
                );
            }
        }
        $url = $this->url('monolyth/account/forgot_pass');
        throw new HTTP301_Exception($url);
    }
}

