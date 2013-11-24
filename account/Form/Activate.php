<?php

/**
 * @package monolyth
 * @subpackage account
 */

namespace monolyth\account;
use monolyth\core\Post_Form;

class Activate_Form extends Post_Form
{
    const ERROR_ACTIVE = 'active';
    const ERROR_MISMATCH = 'nomatch';

    public function prepare()
    {
        $this->addHidden('id');
        $this->addHidden('hash');
        $this->addButton(self::BUTTON_SUBMIT, $this->text('./submit'));
        return parent::prepare();
    }
}

