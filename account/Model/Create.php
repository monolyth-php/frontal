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

class Create_Model extends Model implements User_Access
{
    public function __invoke(array $data, $login = true)
    {
        $this->adapter->beginTransaction();
        if ($this->config->account_name_is_email) {
            $data['name'] = $data['email'];
        }
        $user = $this->user;
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
        if ($hash = $this->pass->hash()) {
            $fields['salt'] = $this->pass->salt();
            $fields['pass'] = "$hash:".hash(
                $hash, 
                $data['pass'].$fields['salt']
            );
        }
        if (isset($data['status'])) {
            $fields['status'] |= $data['status'];
        }

        // Attempt to save basic information.
        if ($this->account->exists('name', $fields['name'])) {
            $this->adapter->rollback();
            return 'name';
        }
        if ($this->account->exists('email', $fields['email'])) {
            $this->adapter->rollback();
            return 'email';
        }
        try {
            $this->adapter->insert('monolyth_auth', $fields);
            $id = $this->adapter->lastInsertId('monolyth_auth_id_seq');
            $this->load($this->adapter->row(
                'monolyth_auth',
                '*',
                ['id' => $id]
            ));
            $data['id'] = $this['id'];
        } catch (InsertNone_Exception $e) {
            $this->adapter->rollback();
            return 'insert failed';
        } catch (Exception $e) {
            $this->adapter->rollback();
            return 'generic';
        }
        if ($login) {
            $_POST['name'] = $data['email'];
            $_POST['pass'] = $data['pass'];
            $form = $this->form->addSource([
                'name' => $data['email'],
                'pass' => $data['pass'],
            ])->load();
            if ($error = $this->activate->request($id)
                or $error = $this->user->login($form)
            ) {
                $this->adapter->rollback();
                return $error;
            }
        }
        $this->adapter->commit();
        return null;
    }
}

