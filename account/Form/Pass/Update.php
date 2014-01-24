<?php

/**
 * @package monolyth
 * @subpackage account
 */

namespace monolyth\account;
use monolyth\core\Post_Form;
use monolyth\User_Access;

class Update_Pass_Form extends Post_Form
{
    use User_Access;

    public function __construct()
    {
        parent::__construct();
        $this->addPassword('old', $this->text('./old'))
             ->isRequired()
             ->isEqualTo(self::user()->pass(), self::user()->salt());
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

