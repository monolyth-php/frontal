<?php

/**
 * @package monolyth
 * @subpackage account
 */

namespace monolyth\account;
use monolyth\User_Model as Base_Model;
use monolyth\User_Access;
use monolyth\adapter;
use monolyth\adapter\sql\NoResults_Exception;
use monolyth\adapter\sql\DeleteNone_Exception;
use monolyth\adapter\sql\Exception;
use monolyth\core\Post_Form;

class User_Model extends Base_Model implements User_Access, adapter\Access
{
    public function name(Post_Form $form)
    {
        if (call_user_func(
            $this->check,
            $form['pass']->value,
            $this->user->pass(),
            $this->user->salt()
        ) != null) {
            return 'password';
        }
        if ($this->exists('name', $form['name']->value)) {
            return 'exists';
        }
        try {
            $this->adapter->update(
                'monolyth_auth',
                ['name' => $form['name']->value],
                ['id' => $this->user->id()]
            );
            $this->user->name($form['name']->value);
            return null;
        } catch (UpdateNone_Exception $e) {
            return 'unchanged';
        } catch (Exception $e) {
            return 'generic';
        }
    }

    public function email(Post_Form $form)
    {
        if (call_user_func(
            $this->check,
            $form['pass']->value,
            $this->user->pass(),
            $this->user->salt()
        ) != null) {
            return 'password';
        }
        if ($this->exists('email', $form['new']->value)) {
            return 'exists';
        }
        try {
            $this->adapter->update(
                'monolyth_auth',
                ['email' => $form['new']->value],
                ['id' => $this->user->id()]
            );
            $this->user->email($form['new']->value);
            $status = $this->user->status();
            $user = $this->user;
            $status |= $user::STATUS_REACTIVATE
                | $user::STATUS_EMAIL_UNCONFIRMED;
            $user->status($status);
            $this->activate->request($user->id());
            return null;
        } catch (UpdateNone_Exception $e) {
            return 'unchanged';
        } catch (Exception $e) {
            return 'generic';
        }
    }

    public function exists($field, $value, $id = null)
    {
        $where = ["LOWER($field)" => strtolower($value)];
        if (isset($id)) {
            $where['id'] = ['<>' => $id];
        }
        try {
            $this->adapter->field('monolyth_auth', 1, $where);
            return true;
        } catch (NoResults_Exception $e) {
            return false;
        }
    }

    public function delete()
    {
        $this->adapter->beginTransaction();
        try {
            $this->adapter->delete(
                'monolyth_auth',
                ['id' => $this->user->id()]
            );
            call_user_func($this->user->logout);
            $this->adapter->commit();
            if (method_exists($this, 'notify')) {
                $this->notify();
            }
            return null;
        } catch (DeleteNone_Exception $e) {
            $this->adapter->rollback();
            return 'generic';
        }
    }
}

