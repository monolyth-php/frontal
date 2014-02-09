<?php

/**
 * @package monolyth
 * @subpackage account
 */

namespace monolyth\account;
use monolyth\User_Access;
use monolyth\core\Model;
use monolyth\adapter\sql\InsertNone_Exception;
use Exception;
use monolyth\Config;

class Create_Model extends Model
{
    use User_Access;

    public function __invoke(array $data, $login = true)
    {
        self::adapter()->beginTransaction();
        $config = Config::get('monolyth');
        if ($config->account_name_is_email) {
            $data['name'] = $data['email'];
        }
        $user = self::user();
        $fields = [
            'name' => $data['name'],
            'email' => $data['email'],
            'ipcreated' => $_SERVER['REMOTE_ADDR'],
            'ipmodified' => $_SERVER['REMOTE_ADDR'],
            'status' => $user::STATUS_EMAIL_UNCONFIRMED
                      | $user::STATUS_ACTIVATE,
            'feature' => 0,
        ];
        $fields['feature'] |= $user::FEATURE_NEWS;
        if (array_key_exists('optin', $data) && $data['optin'] == 1) {
            $fields['feature'] |= $user::FEATURE_OPTIN;
        }
        $pass = new Pass_Model;
        if ($hash = $pass->hash()) {
            $fields['salt'] = $pass->salt();
            $fields['pass'] = "$hash:".hash(
                $hash, 
                $data['pass'].$fields['salt']
            );
        }
        if (isset($data['status'])) {
            $fields['status'] |= $data['status'];
        }

        // Attempt to save basic information.
        $account = new User_Model;
        if ($account->exists('name', $fields['name'])) {
            self::adapter()->rollback();
            return 'name';
        }
        if ($account->exists('email', $fields['email'])) {
            self::adapter()->rollback();
            return 'email';
        }
        try {
            self::adapter()->insert('monolyth_auth', $fields);
            $id = self::adapter()->lastInsertId('monolyth_auth_id_seq');
            $this->load(self::adapter()->row(
                'monolyth_auth',
                '*',
                ['id' => $id]
            ));
            $data['id'] = $this['id'];
        } catch (InsertNone_Exception $e) {
            self::adapter()->rollback();
            return 'insert failed';
        } catch (Exception $e) {
            self::adapter()->rollback();
            return 'generic';
        }
        if ($login) {
            $_POST['name'] = $data['email'];
            $_POST['pass'] = $data['pass'];
            $form = new Login_Form;
            $form->addSource([
                'name' => $data['email'],
                'pass' => $data['pass'],
            ])->load();
            if ($error = (new Activate_Model)->request($id)
                or $error = self::user()->login($form)
            ) {
                self::adapter()->rollback();
                return $error;
            }
        }
        self::adapter()->commit();
        return null;
    }
}

