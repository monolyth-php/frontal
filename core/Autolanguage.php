<?php

/**
 * @package monolyth
 */

namespace monolyth\core;
use monolyth\HTTP301_Exception;
use monolyth\Language_Model;

trait Autolanguage
{
    /**
     * Default autolanguage action.
     *
     * For a multilingual site, you'll usually want to redirect to some language
     * that will make sense to the user.
     *
     * @return void
     */
    protected function _get(array $args)
    {
        if (!isset($args['format'])) {
            $args['format'] = '/%s/';
        }
        throw new HTTP301_Exception(sprintf(
            $args['format'],
            $this->guessLanguage()
        ));
    }

    /**
     * Guess the current language based on Apache's $_SERVER info.
     *
     * @return string The language code found.
     * @todo Make this work for other servers than Apache.
     */
    protected function _guessLanguage(Language_Model $languages = null)
    {
        if (!isset($languages)) {
            $languages = $this->language;
        }
        $default = str_replace(
            '_',
            '-',
            strtolower($languages->default->code)
        );
        if (isset($_COOKIE['language'])
            && $languages->isAvailable($_COOKIE['language'])
        ) {
            return $_COOKIE['language'];
        }
        $options = [$default => 0];
        if (isset($_SERVER['HTTP_ACCEPT_LANGUAGE'])) {
            $parts = preg_split('@,\s*@', $_SERVER['HTTP_ACCEPT_LANGUAGE']);
            foreach ($parts as $part) {
                $code = strtolower(array_shift(explode(';', $part)));
                if (preg_match('@;q=(.*?)$@', $part, $match)) {
                    $weight = $match[1];
                } else {
                    $weight = 1;
                }
                $options[$code] = $weight;
            }
            asort($options);
        }
        foreach (array_reverse($options) as $lan => $weight) {
            $try = str_replace('-', '_', strtolower($lan));
            if (
                isset($languages->$try) &&
                in_array($languages->$try, $languages->available)
            ) {
                return $try;
            }
            $parts = array_unique(array_reverse(preg_split(
                '/[-_]/',
                strtolower($lan)
            )));
            foreach ($parts as $try) {
                if (isset($languages->$try)
                    && in_array($languages->$try, $languages->available)
                ) {
                    if (count($parts) == 2
                        && $parts[0] != $parts[1]
                        && $try == $parts[0]
                        && isset($languages->{$parts[1]})
                        && $languages->$try === $languages->{$parts[1]}
                    ) {
                        return $parts[1];
                    }
                    return $try;
                }
            }
        }
    }
}

