<?php

/**
 * @package Monolyth
 */

namespace Monolyth;

use Improse\View;
use Reroute\Router;

class Autolanguage extends View
{
    private $router;
    private $state;
    private $arguments;

    public function __construct(Router $router, $state, array $arguments = [])
    {
        $this->router = $router;
        $this->state = $state;
        $this->arguments = $arguments;
    }

    /**
     * Default autolanguage action.
     *
     * For a multilingual site, you'll usually want to redirect to some language
     * that will make sense to the user.
     *
     * @return void
     */
    public function guess($argument_name, array $languages = ['en'])
    {
        if (isset($_COOKIE['language'])
            && in_array($_COOKIE['language'], $languages)
        ) {
            $language = $_COOKIE['language'];
        } else {
            $options = [];
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
                arsort($options);
            }
            foreach ($options as $try => $weight) {
                if (in_array($try, $languages)) {
                    $language = $try;
                    break;
                }
            }
        }
        if (!isset($language)) {
            $language = $languages[0];
        }
        $this->arguments[$argument_name] = $language;
        $this->router->get($this->state)->url()->move($this->arguments);
    }
}

