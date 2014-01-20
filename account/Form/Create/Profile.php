<?php

/**
 * @package monolyth
 * @subpackage account
 */

namespace monolyth\account;
use monolyth\core\Post_Form;

/**
 * The registration form.
 */
class Profile_Create_Form extends Post_Form
{
    const ERROR_EXISTS = 'exists';

    public function __construct()
    {
        parent::__construct();
        $this->account = new User_Model;
        $this->addText('name', $this->text('./name'))
             ->isRequired()
             ->addTest(function($value) {
                if ($this->account->exists('name', $value)) {
                    return $this::ERROR_EXISTS;
                } else {
                    return null;
                }
             });
        $this->addEmail('email', $this->text('./email'))
             ->isRequired()
             ->addTest(function($value) {
                if ($this->account->exists('email', $value)) {
                    return $this::ERROR_EXISTS;
                } else {
                    return null;
                }
             });
        $this->addPassword('pass', $this->text('./pass'))->isRequired();
        $this->addButton(self::BUTTON_SUBMIT, $this->text('create/next'));
        return parent::prepare();
    }
}

