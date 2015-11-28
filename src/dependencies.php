<?php

use Disclosure\Container;
use Dabble\Adapter\Sqlite;
use Envy\Envy;

/**
 * Example dependencies for use with Disclosure.
 */

/**
 * Example Envy configuration. See http://envy.monomelodies.nl for more info.
 */
Container::inject('*', function (&$env) {
    $env = new Envy(dirname(__DIR__).'/Envy.json', function ($env) {
        $envs = [];
        if (isset($_SERVER['HTTP_HOST'])) {
            $envs[] = 'web';
            if (preg_match(
                '@\localhost$@',
                $_SERVER['HTTP_HOST']
            )) {
                $envs[] = 'dev';
            } else {
                $envs[] = 'prod';
            }
        } elseif (class_exists('PHPUnit_Framework_TestCase', false)) {
            $envs[] = 'test';
        } else {
            $envs[] = 'cli';
            if (php_uname('n') == 'name-of-your-machine') {
                $envs[] = 'dev';
            } else {
                $envs[] = 'prod';
            }
        }
        if (in_array('dev', $envs)) {
            error_reporting(E_ALL & ~E_STRICT);
            ini_set('display_errors', 'On');
            if (in_array('web', $envs)) {
                $env->host = "http://{$_SERVER['HTTP_HOST']}";
            } elseif (in_array('cli', $envs)) {
                $env->user = get_current_user();
            }
        }
        return $envs;
    });
});

/**
 * Setup a database connection. This uses Sqlite with an in-memory temporary
 * database; presumably you'll want something different in the real world ;)
 */
Container::inject('*', function (&$adapter) {
    $adapter = new Sqlite(':memory:');
    $adapter->exec(file_get_contents(
        '../vendor/monomelodies/cesession/info/sql/sqlite.sql'
    ));
});

/*
 * This example injects Twig into the base View. Feel free to use a different
 * templating engine, e.g. Moustache).
 */
$router = require 'router.php';
View::inject(function (&$twig) use ($router) {
    $loader = new Twig_Loader_Filesystem(__DIR__);
    $twig = new Twig_Environment($loader, [
        // This will depend on your preferences.
        'cache' => dirname(__DIR__).'/.twig-cache',
        // Set there two o false for production; e.g. use monomelodies/envy to
        // handle your environments.
        'debug' => true,
        'auto_reload' => true,
    ]);
    
    // Example url function, assuming `$router` is available:
    $twig->addFunction(new Twig_SimpleFunction('url', [$router, 'generate']));
    
    /**
     * Example integration of Metaculous. (See
     * http://metaculous.monomelodies.nl, usage is of course optional.)
     */
    $arrayOfIgnoreWords = $hashOfRiggedWords = [];
    $twig->addExtension(new Metaculous\TwigExtension(
        $arrayOfIgnoreWords,
        $hashOfRiggedWords
    ));
});

