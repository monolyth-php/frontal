<?php

/**
 * @package monolyth
 * @subpackage account
 */

namespace monolyth\account;
use monolyth\core\Post_Form;
use monolyth\User_Access;

class Update_Pass_Form extends Post_Form implements User_Access
{
    public function prepare()
    {
        $this->addPassword('old', $this->text('./old'))
             ->isRequired()
             ->isEqualTo($this->user->pass(), $this->user->salt());
        $this->addPassword('new', $this->text('./new'))
             ->isRequired()
             ->differsFromField($this, 'old');
        $this->addPassword('new2', $this->text('./new2'))
             ->isRequired()
             ->matchesField($this, 'new');
        $this->addButton(self::BUTTON_SUBMIT, $this->text('./submit'));
        return parent::prepare();
    }
}

