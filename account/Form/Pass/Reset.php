<?php

/**
 * @package monolyth
 * @subpackage account
 */

namespace monolyth\account;
use monolyth\core\Post_Form;
use monolyth\adapter\sql\NoResults_Exception;
use Adapter_Access;

class Reset_Pass_Form extends Post_Form
{
    use Adapter_Access;

    const ERROR_NOMATCH = 1;

    public function __construct()
    {
        parent::__construct();
        $id = isset($_POST['id']) ? $_POST['id'] : 0;
        $this->addHidden('id')->isRequired();
        $this->addHidden('hash')->isRequired();
        $error = self::ERROR_NOMATCH;
        $this->addText('name', $this->text('./name'))
             ->isRequired()
             ->addTest(function($value) use($error, $id) {
                try {
                    return $id == self::adapter()->field(
                        'monolyth_auth',
                        'id',
                        ['LOWER(name)' => strtolower($value)]
                    ) ? null : $error;
                } catch (NoResults_Exception $e) {
                    return $error;
                }
             });
        $this->addButton(self::BUTTON_SUBMIT, $this->text('./submit'));
        return parent::prepare();
    }
}

