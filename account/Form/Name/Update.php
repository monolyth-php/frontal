<?php

namespace monolyth\account;
use monolyth\core\Post_Form;
use adapter\Access as Adapter_Access;
use monolyth\User_Access;
use monolyth\adapter\sql\NoResults_Exception;

class Update_Name_Form extends Post_Form
{
    use Adapter_Access;
    use User_Access;

    public function __construct()
    {
        parent::__construct();
        $this->addText('name', $this->text('./name'))
             ->isRequired()
             ->isNotEqualTo(self::user()->name())
             ->mustMatch(self::user()::MATCH_NAME)
             ->addTest(function($value) use(self::adapter()) {
                try {
                    self::adapter()->field(
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

