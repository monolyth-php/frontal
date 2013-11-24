<?php

/**
 * @package monolyth
 * @subpackage account
 */

namespace monolyth\account;
use monolyth\core\Post_Form;

class Forgot_Pass_Form extends Post_Form
{
    public function prepare()
    {
        if (!$this->config->account_name_is_email) {
            $this->addText('name', $this->text('./name'))->isRequired();
        }
        $this->addEmail('email', $this->text('./email'))->isRequired();
        $this->addButton(self::BUTTON_SUBMIT, $this->text('./submit'));
        return parent::prepare();
    }
}

