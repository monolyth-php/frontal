<?php

namespace monolyth\utils;
use adapter\Access as Adapter_Access;
use monolyth\Project_Access;
use monolyth\adapter\sql\InsertNone_Exception;

class Cookie_Model
{
    use Adapter_Access;
    use Project_Access;

    public function store(array $settings)
    {
        $save = 0;
        $match = ['e' => 1];
        foreach ($match as $key => $bit) {
            if (isset($settings[$key]) && $settings[$key]) {
                $save |= $bit;
            }
        }
        try {
            self::adapter()->insert(
                'monolyth_cookie',
                [
                    'id' => $_COOKIE['mocoid'],
                    'ip' => $_SERVER['REMOTE_ADDR'],
                    'user_agent' => $_SERVER['HTTP_USER_AGENT'],
                    'settings' => $save,
                ]
            );
            setcookie(
                'mocoqu',
                1,
                time() + 60 * 60 * 24 * 3650,
                '/',
                self::project()['cookiedomain']
            );
            setcookie(
                'mocook',
                $save,
                time() + 60 * 60 * 24 * 3650,
                '/',
                self::project()['cookiedomain']
            );
        } catch (InsertNone_Exception $e) {
            $this->generateId();
            $this->store($settings);
        }
        return null;
    }

    public function generateId()
    {
        $value = sha1(time().serialize($_SERVER).rand(0, 99999));
        setcookie(
            'mocoid',
            $value,
            time() + 60 * 60 * 24 * 3650,
            '/',
            self::project()['cookiedomain']
        );
        $_COOKIE['mocoid'] = $value;
    }
}

