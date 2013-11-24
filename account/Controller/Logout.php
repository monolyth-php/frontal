<?php

/**
 * @package monolyth
 * @subpackage account
 */

namespace monolyth\account;
use monolyth;

class Logout_Controller extends monolyth\Controller
{
    protected function get(array $args)
    {
        if ($redir = $this->http->getGet('redir')) {
            $redir = urldecode($redir);
        } elseif (isset($_SERVER['HTTP_REFERER'])) {
            $redir = $_SERVER['HTTP_REFERER'];
        } else {
            try {
                $redir = $this->url('');
            } catch (monolyth\utils\RouteNotFound_Exception $e) {
                $redir = '/';
            }
        }
        $this->user->logout($redir);
        throw new monolyth\HTTP301_Exception($redir);
    }
}

