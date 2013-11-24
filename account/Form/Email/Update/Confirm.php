<?php

/**
 * @package monolyth
 * @subpackage account
 */

namespace monolyth\account;
use monolyth\form;

class Confirm_Update_Email_Form extends Update_Email_Form
{
    const ERROR_MISMATCH = 1;

    public function prepare()
    {
        $this->addText('new2', $this->text('./new2'))
             ->isRequired()
             ->matchesField($this, 'new');
        return parent::prepare();
    }
}

