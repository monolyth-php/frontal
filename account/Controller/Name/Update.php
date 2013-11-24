<?php

namespace monolyth\account;
use monolyth;

class Update_Name_Controller extends monolyth\Controller 
implements monolyth\Login_Required
{
    public function get()
    {
        return $this->view('page/name/update');
    }

    public function post()
    { 
        if (!$this->form->errors()) {
            if ($error = $this->update->name($this->form)) {
                $this->message->add(
                    self::MESSAGE_ERROR,
                    $this->text("./error.$error")
                );
            } else {
                $this->message->add(
                    self::MESSAGE_SUCCESS,
                    $this->text('./success')
                );
                throw new monolyth\HTTP301_Exception($this->http->getSelf());
            }
        }
        return $this->get(); 
    }
}

