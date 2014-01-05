<?php

/**
 * @package monolyth
 * @subpackage account
 */

namespace monolyth\account;
use monolyth\User_Model as Base_Model;
use monolyth\User_Access;
use adapter\Access as Adapter_Access;
use monolyth\adapter\sql\NoResults_Exception;
use monolyth\adapter\sql\DeleteNone_Exception;
use monolyth\adapter\sql\Exception;
use monolyth\core\Post_Form;

class User_Model extends Base_Model
{
    use Adapter_Access;
    use User_Access;

    public function name(Post_Form $form)
    {
        if (call_user_func(
            $this->check,
            $form['pass']->value,
            self::user()->pass(),
            self::user()->salt()
        ) != null) {
            return 'password';
        }
        if ($this->exists('name', $form['name']->value)) {
            return 'exists';
        }
        try {
            self::adapter()->update(
                'monolyth_auth',
                ['name' => $form['name']->value],
                ['id' => self::user()->id()]
            );
            self::user()->name($form['name']->value);
            return null;
        } catch (UpdateNone_Exception $e) {
            return 'unchanged';
        } catch (Exception $e) {
            return 'generic';
        }
    }

    public function email(Post_Form $form)
    {
        $check = new Check_Pass_Model;
        if (call_user_func(
            $check,
            $form['pass']->value,
            self::user()->pass(),
            self::user()->salt()
        ) != null) {
            return 'password';
        }
        if ($this->exists('email', $form['new']->value)) {
            return 'exists';
        }
        try {
            self::adapter()->update(
                'monolyth_auth',
                ['email' => $form['new']->value],
                ['id' => self::user()->id()]
            );
            self::user()->email($form['new']->value);
            $status = self::user()->status();
            $user = self::user();
            $status |= $user::STATUS_REACTIVATE
                | $user::STATUS_EMAIL_UNCONFIRMED;
            $user->status($status);
            $activate = new Activate_Model;
            $activate->request($user->id());
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
            self::adapter()->field('monolyth_auth', 1, $where);
            return true;
        } catch (NoResults_Exception $e) {
            return false;
        }
    }

    public function delete()
    {
        self::adapter()->beginTransaction();
        try {
            self::adapter()->delete(
                'monolyth_auth',
                ['id' => self::user()->id()]
            );
            call_user_func(self::user()->logout);
            self::adapter()->commit();
            if (method_exists($this, 'notify')) {
                $this->notify();
            }
            return null;
        } catch (DeleteNone_Exception $e) {
            self::adapter()->rollback();
            return 'generic';
        }
    }
}

