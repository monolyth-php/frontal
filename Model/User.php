<?php

/**
 * @package monolyth
 */

namespace monolyth;
use ErrorException;

class User_Model implements Session_Access, Message_Access, User_Constants
{
    use utils\Translatable, render\Url_Helper;

    public function getArrayCopy()
    {
        return $this->session->get('User');
    }

    public function loggedIn()
    {
        try {
            return (bool)$this->session->get('User')['id'];
        } catch (ErrorException $e) {
            return false;
        }
    }

    public function get($field)
    {
        try {
            return $this->session->get('User')[$field];
        } catch (ErrorException $e) {
            return null;
        }
    }

    public function getNameById($id)
    {
        return $this->adapter->field('monolyth_auth', 'name', compact('id'));
    }

    public function group()
    {
        try {
            return $this->session->get('Group');
        } catch (ErrorException $e) {
            return null;
        }
    }

    public function __call($name, $arguments)
    {
        if ($arguments) {
            $User = $this->session->get('User');
            $User[$name] = array_shift($arguments);
            $this->session->set(compact('User'));
        }
        return $this->get($name);
    }

    public function id2dir($id)
    {
        return implode('/', str_split($id, 3));
    }

    public function login(core\Post_Form $form, $salted = false)
    {
        if (!($error = call_user_func($this->login, $form, $salted))) {
            if ($this->status() & $this::STATUS_GENERATED_PASS) {
                $message = $this->message;
                $message->add(
                    $message::INFO,
                    $this->text(
                        'monolyth\account\pass/generated',
                        $this->url('monolyth/account/update_pass')
                    )
                );                    
            }
            $this->acl->flush();
        }
        return $error;
    }

    public function logout(&$redir = null)
    {
        call_user_func_array($this->logout, [&$redir]);
    }

    public function active()
    {
        return !($this->status() & $this::STATUS_INACTIVE);
    }
}

