<?php

/**
 * @package monolyth
 * @subpackage account
 */

namespace monolyth\account;
use monolyth\Controller;
use monolyth\HTTP301_Exception;
use monolyth\Login_Required;

class Update_Controller extends Controller implements Login_Required
{
    public function __construct()
    {
        parent::__construct();
        $this->form = new Update_Form;
    }

    protected function get(array $args)
    {
        $this->form->addSource(self::session()->get('User'))
                   ->load();
        return $this->view('page/update');
    }

    protected function post(array $args)
    {
        $View = $this->get($args);
        $data = $View->data();
        if (!$this->form->errors()) {
            if (!$error = (new Update_Model)->save($this->form)) {
                self::message()->add(
                    'success',
                    $this->text('./success')
                );
                throw new HTTP301_Exception(self::http()->getSelf());
            } else {
                self::message()->add(
                    'error',
                    $this->text("./error.$error")
                );
            }
        }
        return $View;
    }
}

