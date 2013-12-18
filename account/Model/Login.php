<?php

/**
 * @package monolyth
 * @subpackage account
 */

namespace monolyth\account;
use ErrorException;
use monolyth\Project_Access;
use monolyth\Session_Access;
use monolyth\adapter;
use monolyth\adapter\sql\NoResults_Exception;
use monolyth\adapter\sql\UpdateNone_Exception;
use monolyth\adapter\nosql\KeyNotFound_Exception;

class Login_Model implements adapter\Access, Project_Access, Session_Access
{
    public function __invoke(Login_Form $form, $salted = false)
    {
        try {
            $u = $this->adapter->row(
                'monolyth_auth',
                '*',
                [[
                    'LOWER(name)' => strtolower($form['name']->value),
                    'LOWER(email)' => strtolower($form['name']->value),
                ]]
            );
        } catch (NoResults_Exception $e) {
            $this->session->set('User', null);
            return 'nomatch';
        }
        if ($error = call_user_func(
            $this->pass,
            $form['pass']->value,
            $u['pass'],
            $u['salt'],
            $salted
        )) {
            $this->session->set('User', null);
            if ($error = $this->fail($form)) {
                return $error;
            }
            return 'nomatch';
        }
        $User = [];
        foreach ($u as $property => $data) {
            $User[$property] = $data;
        }
        $this->session->set(compact('User'));
        $id = substr(session_id(), 0 ,32);
        $random = substr(session_id(), 32);
        try {
            $this->adapter->update(
                'monolyth_session',
                ['userid' => null],
                ['userid' => $u['id']]
            );
        } catch (UpdateNone_Exception $e) {
            // Not already logged in; that's fine.
        }
        try {
            $this->adapter->update(
                'monolyth_session',
                [
                    'userid' => $u['id'],
                    'data' => base64_encode(serialize($this->session->all())),
                ],
                ['id' => $id, 'randomid' => $random]
            );
        } catch (UpdateNone_Exception $e) {
            // Okay, this is a problem: we couldn't log ourselves in.
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
                md5($u['name'].$u['pass'].$u['salt'].$this->http->ip()
                    .$this->http->userAgent()),
                time() + 60 * 60 * 24 * 365 * 10, // Valid for ten years.
                '/',
                preg_replace(
                    '@^(www\.|secure\.)@',
                    '',
                    $this->http->server()
                ),
                false,
                true
            );
        }
        // Cache some basic ACL info:
        $Groups = [];
        try {
            $q = $this->adapter->rows(
                'monolyth_auth_group AS a
                 JOIN monolyth_auth_link_auth_group l
                 ON a.id = l.auth_group',
                ['name', 'auth_group'],
                ['l.auth' => $u['id']]
            );
            foreach ($q as $row) {
                $Groups[$row['auth_group']] = $row['name'];
            }
        } catch (NoResults_Exception $e) {
            // No problem; not all users belong to groups per se.
        }
        $this->session->set(compact('Groups'));
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

