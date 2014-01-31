<?php

/**
 * @package monolyth
 */

namespace monolyth;
use Project;

class Redirect_Controller extends core\Controller
{
    /**
     * Redirect to another location.
     *
     * The standard method of redirection to a different page. Monolyth tries by
     * default to redirect in any way possible, meaning first via the headers,
     * then via meta-refresh and finally via javascript's window.location.
     * The default view for this (template/redirect) 
     * can, of course, be overridden.
     *
     * @param string|null $url The URI to redirect to. Defaults to the current
     *                         location.
     * @param int $status The HTTP status code to pass.
     */
     protected function get(array $args)
     {
        extract($args);
        if (!isset($url, $code)) {
            throw new HTTP404_Exception;
        }
        static $_url = null;
        if (!$url) {
            $url = isset($_url) ? $_url : self::http()->getSelf();
            $_url = null;
        }
        $parts = parse_url($url);
        $key = 'protocol';
        $scheme = Project::instance()['secure'] ?
            Project::instance()['protocols'] :
            Project::instance()['protocol'];
        $fallback = parse_url(
            "$scheme://{$_SERVER['HTTP_HOST']}{$_SERVER['REQUEST_URI']}"
        );
        if (!isset($fallback['scheme'])) {
            $fallback['scheme'] = $scheme;
        }
        if (!isset($fallback['host'])) {
            $fallback['host'] = $_SERVER['SERVER_NAME'];
        }
        $new_query = isset($parts['query']) ?
            self::parseQuery($parts['query']) :
            [];
        $userinfo = '';
        if (isset($parts['user']) || isset($fallback['user'])) {
            // userinfo: user:pass@
            // if not in parts or fallback, ignore
            // if only in fallback, check if host is in parts
            $user = '';
            $pass = '';
            if (!isset($parts['user']) && !isset($parts['host'])) {
                // prolly getting whole thing from default
                if (isset($fallback['pass'])) {
                    $userinfo = ':'.$fallback['pass'];
                }
                $userinfo = $parts['user'].$userinfo.'@';
            }
        }
        $port = '';
        if (isset($parts['port']) && $parts['port'] != 80) {
            $port = ':'.$parts['port'];
        } elseif (!isset($parts['port'])
            && !isset($parts['host']) && isset($fallback['port'])
        ) {
            $port = ':'.$fallback['port'];
        }
        $url = sprintf(
            "%s://%s%s%s%s%s%s",
            isset($parts['scheme']) ? $parts['scheme'] : $fallback['scheme'],
            $userinfo,
            isset($parts['host']) ? $parts['host'] : $fallback['host'],
            $port,
            isset($parts['path']) ? $parts['path'] : '/',
            $this->buildQuery($new_query),
            isset($parts['fragment']) ? "#{$parts['fragment']}" : ''
        );
        header("Location: $url", true, $code);
        $this->template = null;
        return $this->view('template/redirect', ['url' => $url]);
    }

    /**
     * Private helper to parse the query part of a URI.
     *
     * @param string $query The query_string part of a URI.
     * @return array Array of arguments with key/value coupling.
     */
    private function parseQuery($query)
    {
        $args = [];
        $els = explode('&', $query);
        foreach ($els as $el) {
            $parts = explode('=', $el);
            $args[$parts[0]] = isset($parts[1]) ? $parts[1] : NULL;
        }
        return $args;
    }
    
    /**
     * Private helper to contruct a valid query part for a URI.
     *
     * @param array $q $_GET-parts of a URI.
     * @return string Appendable string with ?$_SERVER['QUERY_STRING']
     */
    private function buildQuery($q)
    {
        if (!$q) {
            return '';
        }
        $new = [];
        foreach ($q as $name => $value) {
            $new[] = sprintf('%s=%s', $name, $value);
        }
        return '?'.implode('&', $new);
    }
}

