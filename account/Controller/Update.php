<?php

/**
 * @package monolyth
 * @subpackage account
 */

namespace monolyth\account;
use monolyth\Controller;
use monolyth\HTTP301_Exception;
use monolyth\Session_Access;
use monolyth\Login_Required;

class Update_Controller extends Controller
implements Session_Access, Login_Required
{
    protected function get(array $args)
    {
        $this->form->addSource($this->session->get('User'))
                   ->load();
        return $this->view('page/update');
    }

    protected function post(array $args)
    {
        $View = $this->get($args);
        $data = $View->data();
        if (!$this->form->errors()) {
            if (!$error = $this->update->save($this->form)) {
                $this->message->add(
                    self::MESSAGE_SUCCESS,
                    $this->text('./success')
                );
                throw new HTTP301_Exception($this->http->getSelf());
            } else {
                $this->message->add(
                    self::MESSAGE_ERROR,
                    $this->text("./error.$error")
                );
            }
        }
        return $View;
    }
}

