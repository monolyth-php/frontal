<?php

namespace monolyth\account;
use monolyth\core\Model;
use monolyth\User_Access;
use monolyth\core\Post_Form;
use monolyth\adapter\sql\NoResults_Exception;

class Auto_Login_Model extends Model
{
    use User_Access;

    public function __construct()
    {
        parent::__construct();
        $this->form = new Login_Form;
    }

    public function __invoke($hash)
    {
        try {
            $auth = self::adapter()->row(
                'monolyth_auth',
                '*',
                [sprintf(
                    "md5(CONCAT(name, pass, COALESCE(salt, ''), %s, %s))",
                    self::adapter()->quote($_SERVER['REMOTE_ADDR']),
                    self::adapter()->quote($_SERVER['HTTP_USER_AGENT'])
                ) => $hash]
            );
            $this->form['name']->value = $auth['name'];
            $this->form['pass']->value = $auth['pass'];
            self::user()->login($this->form, true);
        } catch (NoResults_Exception $e) {
        }
    }
}

