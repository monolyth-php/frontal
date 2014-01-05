<?php

/**
 * @package monolyth
 * @subpackage account
 */

namespace monolyth\account;
use monolyth\core\Model;
use monolyth\Project_Access;
use monolyth\Session_Access;
use adapter\Access as Adapter_Access;
use monolyth\adapter;

class Logout_Model extends Model
{
    use Project_Access;
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
                        self::project()['site'],
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

