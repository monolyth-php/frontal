<?php

/**
 * @package monolyth
 * @subpackage account
 */

namespace monolyth\account;
use ErrorException;
use monolyth\Session_Access;
use monolyth\HTTP_Access;
use Adapter_Access;
use monolyth\adapter\sql\NoResults_Exception;
use monolyth\adapter\sql\UpdateNone_Exception;
use monolyth\adapter\nosql\KeyNotFound_Exception;
use Project;

class Login_Model
{
    use Adapter_Access;
    use Session_Access;
    use HTTP_Access;

    public function __construct()
    {
        $this->pass = new Check_Pass_Model;
    }

    public function __invoke(Login_Form $form, $salted = false)
    {
        try {
            $u = self::adapter()->row(
                'monolyth_auth',
                '*',
                [[
                    'LOWER(name)' => strtolower($form['name']->value),
                    'LOWER(email)' => strtolower($form['name']->value),
                ]]
            );
        } catch (NoResults_Exception $e) {
            self::session()->set('User', null);
            return 'nomatch';
        }
        if ($error = call_user_func(
            $this->pass,
            $form['pass']->value,
            $u['pass'],
            $u['salt'],
            $salted
        )) {
            self::session()->set('User', null);
            if ($error = $this->fail($form)) {
                return $error;
            }
            return 'nomatch';
        }
        $User = [];
        foreach ($u as $property => $data) {
            $User[$property] = $data;
        }
        $id = substr(session_id(), 0 ,32);
        $random = substr(session_id(), 32);
        try {
            self::adapter()->update(
                'monolyth_session',
                ['userid' => null],
                ['userid' => $u['id']]
            );
        } catch (UpdateNone_Exception $e) {
            // Not already logged in; that's fine.
        }
        try {
            self::adapter()->update(
                'monolyth_session',
                [
                    'userid' => $u['id'],
                    'data' => base64_encode(serialize(self::session()->all())),
                ],
                ['id' => $id, 'randomid' => $random]
            );
        } catch (UpdateNone_Exception $e) {
            // Okay, this is a problem: we couldn't log ourselves in.
            // Most probable cause is that the session was in memcached,
            // but had gotten deleted from the database.
            setcookie(
                \Project::instance()['site'],
                '',
                time() - 3600,
                '/',
                \Project::instance()['cookiedomain'],
                false,
                false
            );
            return 'generic';
        }
        if (isset($this->cache)) {
            // Flush memcached session.
            try {
                $this->cache->delete("{$this->project['site']}/$id/$random");
            } catch (KeyNotFound_Exception $e) {
                // Fine.
            }
        }
        if (isset($form['remember']) && $form['remember']->isChecked()) {
            setcookie(
                'monolyth_persist',
                md5($u['name'].$u['pass'].$u['salt'].self::http()->ip()
                    .self::http()->userAgent()),
                time() + 60 * 60 * 24 * 365 * 10, // Valid for ten years.
                '/',
                preg_replace(
                    '@^(www\.|secure\.)@',
                    '',
                    self::http()->server()
                ),
                false,
                true
            );
        }
        // Cache some basic ACL info:
        $Groups = [];
        try {
            $q = self::adapter()->rows(
                'monolyth_group AS g
                 JOIN monolyth_auth_group ag
                 ON g.id = ag.auth_group',
                ['name', 'auth_group'],
                ['ag.auth' => $u['id']]
            );
            foreach ($q as $row) {
                $Groups[$row['auth_group']] = $row['name'];
            }
        } catch (NoResults_Exception $e) {
            // No problem; not all users belong to groups per se.
        }
        self::session()->set(compact('Groups'));
        $User['roles'] = ['authenticated'];
        foreach ($Groups as $group) {
            $group = preg_replace('@ies$@', 'y', $group);
            $group = preg_replace('@s$@', '', $group);
            $User['roles'][] = strtolower($group);
        }
        self::session()->set(compact('User'));
        return null;
    }

    public function success()
    {
        return null;
    }

    public function fail()
    {
        return null;
    }
}

