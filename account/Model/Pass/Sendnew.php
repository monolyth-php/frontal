<?php

/**
 * @package monolyth
 * @subpackage account
 */

namespace monolyth\account;
use monolyth\adapter\sql\NoResults_Exception;
use monolyth\adapter\sql\InsertNone_Exception;

class Sendnew_Pass_Model extends Reset_Pass_Model
{
    public function __invoke(Forgot_Pass_Form $form)
    {
        if (!($auth = $this->auth($form))) {
            return 'unknown';
        }
        $this->adapter->beginTransaction();
        if ($error = $this->confirm(
            $auth,
            'monolyth\account\pass/send',
            $this->confirm->getFreeHash($auth['id'].$auth['name']),
            $this->generate()
        )) {
            $this->adapter->rollback();
            return $error;
        }
        $this->adapter->commit();
        return null;
    }
}

