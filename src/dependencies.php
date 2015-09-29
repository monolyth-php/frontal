<?php

use Disclosure\Container;
use Dabble\Adapter\Sqlite;

/**
 * Example dependencies for use with Disclosure.

/**
 * Setup a database connection. This uses Sqlite with an in-memory temporary
 * database; presumably you'll want something different in the real world ;)
 */
Container::inject('*', function (&$adapter) {
    $adapter = new Sqlite(':memory:');
});

/*
 * This example injects Twig into the base View, which is probably what you're
 * going to want (although feel free to use a different templating engine, e.g.
 * Moustache).
 */
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
    $url = function ($name, array $args = []) use ($router) {
        if (!isset($_SERVER['HTTP_HOST'])) {
            // Again, you could/should use something like monomelodies/envy to
            // set this to a sane default for your environment.
            $_SERVER['HTTP_HOST'] = 'http://localhost';
        }
        return $router->get($name)->url()->short(
            "http://{$_SERVER['HTTP_HOST']}{$_SERVER['REQUEST_URI']}",
            $args
        );
    };
    $twig->addFunction(new Twig_SimpleFunction('url', $url));
    
    /**
     * Example integration of Metaculous: (See
     * http://metaculous.monomelodies.nl, usage is of course optional.)
     */
    $arrayOfIgnoreWords = $hashOfRiggedWords = [];
    $twig->addExtension(new Metaculous\TwigExtension(
        $arrayOfIgnoreWords,
        $hashOfRiggedWords
    ));
});

