<?php

/**
 * @package monolyth
 * @subpackage account
 */

namespace monolyth\account;
use monolyth\core\Post_Form;
use monolyth\User_Access;

class Update_Email_Form extends Post_Form implements User_Access
{
    const ERROR_MISMATCH = 1;

    public function prepare()
    {
        $this->addEmail('new', $this->text('./new'))
             ->isRequired()
             ->isNotEqualTo($this->user->email());
        $this->addPassword('pass', $this->text('./pass'))
             ->isRequired()
             ->isEqualTo($this->user->pass(), $this->user->salt());
        $this->addButton(self::BUTTON_SUBMIT, $this->text('./submit'));
        return parent::prepare();
    }
}

