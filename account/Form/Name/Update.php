<?php

namespace monolyth\account;
use monolyth\core\Post_Form;
use monolyth\adapter;
use monolyth\User_Access;
use monolyth\adapter\sql\NoResults_Exception;

class Update_Name_Form extends Post_Form implements adapter\Access, User_Access
{
    public function prepare()
    {
        $user = $this->user;
        $adapter = $this->adapter;
        $this->addText('name', $this->text('./name'))
             ->isRequired()
             ->isNotEqualTo($user->name())
             ->mustMatch($user::MATCH_NAME)
             ->addTest(function($value) use($adapter) {
                try {
                    $adapter->field(
                        'monolyth_auth',
                        'name',
                        ['name' => $value]
                    );
                    $text = $this['name'];
                    return $text::ERROR_EXISTS;
                } catch (NoResults_Exception $e) {
                    return null;
                }
             });
        $this->addPassword('pass', $this->text('./pass'))
             ->isRequired()
             ->isEqualTo($this->user->pass(), $this->user->salt());
        $this->addButton(self::BUTTON_SUBMIT, $this->text('./submit'));
        return parent::prepare();
    }
}

