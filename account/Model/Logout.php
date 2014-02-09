<?php

/**
 * @package monolyth
 * @subpackage account
 */

namespace monolyth\account;
use monolyth\core\Model;
use monolyth\Session_Access;
use Adapter_Access;
use monolyth\adapter;
use Project;

class Logout_Model extends Model
{
    use Session_Access;
    use Adapter_Access;

    public function __invoke(&$redir = null)
    {
        unset($_COOKIE['monolyth_persist']);
        setcookie(
            'monolyth_persist',
            '',
            1,
            '/',
            preg_replace(
                '@^(www\.|secure\.)@',
                '',
                $_SERVER['SERVER_NAME']
            ),
            false,
            true
        );
        try {
            self::adapter()->update(
                'monolyth_session',
                ['userid' => null],
                [
                    'id' => substr(session_id(), 0, 32),
                    'randomid' => substr(session_id(), 32),
                ]
            );
        } catch (adapter\sql\UpdateNone_Exception $e) {
            // Session update failed, but that only means the session is
            // invalid anyway.
        }
        self::session()->reset();
        self::session()->write(session_id(), true);
        if (isset(self::session()->cache)) {
            try {
                self::session()->cache->delete(
                    sprintf(
                        'session/%s/%s',
                        Project::instance()['site'],
                        session_id()
                    ),
                    []
                );
            } catch (adapter\nosql\KeyNotFound_Exception $e) {
                // That's fine.
            }
        }
        return null;
    }
}

