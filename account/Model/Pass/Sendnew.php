<?php

/**
 * @package monolyth
 * @subpackage account
 */

namespace monolyth\account;
use monolyth\Confirm_Model;

class Sendnew_Pass_Model extends Reset_Pass_Model
{
    public function __invoke(Forgot_Pass_Form $form)
    {
        if (!($auth = $this->auth($form))) {
            return 'unknown';
        }
        self::adapter()->beginTransaction();
        if ($error = $this->confirm(
            $auth,
            'monolyth\account\pass/send',
            (new Confirm_Model)->getFreeHash($auth['id'].$auth['name']),
            $this->generate()
        )) {
            self::adapter()->rollback();
            return $error;
        }
        self::adapter()->commit();
        return null;
    }
}

