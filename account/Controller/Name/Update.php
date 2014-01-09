<?php

namespace monolyth\account;
use monolyth\Controller;
use monolyth\Login_Required;
use monolyth\HTTP301_Exception;
use monolyth\Message;

class Update_Name_Controller extends Controller implements Login_Required
{
    public function __construct()
    {
        parent::__construct();
        $this->form = new Update_Name_Form;
    }

    public function get()
    {
        return $this->view('page/name/update');
    }

    public function post()
    { 
        if (!$this->form->errors()) {
            if ($error = (new Pass_Model)->name($this->form)) {
                self::message()->add(
                    Message::ERROR,
                    $this->text("./error.$error")
                );
            } else {
                self::message()->add(
                    Message::SUCCESS,
                    $this->text('./success')
                );
                throw new HTTP301_Exception(self::http()->getSelf());
            }
        }
        return $this->get(); 
    }
}

