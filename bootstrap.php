<?php

namespace monolyth;
use ErrorException;

/**
 * Require the Monolyth autoloader, since otherwise we don't know where to
 * find stuff.
 *
 * @see Monolyth\Autoload
 */
require_once realpath(__DIR__).'/Autoload.php';

/**
 * All lowercase namespaces are considered a "submodule", which allows
 * us to place submodules in their own repository but still fall under the
 * global namespace of their "parent" module. For example:
 * In the namespace monolyth we have authentication helpers in
 * monolyth\auth. To allow separate cloning and substitution by the same
 * name, we store this in monolyth-auth instead.
 * If a "submodule" could not be found, try the "normal" version instead
 * (which just treats all lowercase namespaces as directories verbatim).
 */
spl_autoload_register(function($class) {
    $namespaces = explode('\\', $class);
    $class = array_pop($namespaces);

    $modules = [];
    while (true) {
        if (!$namespaces) {
            break;
        }
        if ($namespaces[0] == strtolower($namespaces[0])) {
            $modules[] = array_shift($namespaces);
        } else {
            break;
        }
    }
    foreach (['-', DIRECTORY_SEPARATOR] as $type) {
        $file = sprintf(
            '%s%s%s.php',
            $modules ? implode($type, $modules).DIRECTORY_SEPARATOR : '',
            $namespaces ?
                implode(DIRECTORY_SEPARATOR, $namespaces).DIRECTORY_SEPARATOR :
                '',
                $class
        );
        // Use fopen so it supports include_path:
        try {
            $fp = @fopen($file, 'r', true);
            if ($fp) {
                fclose($fp);
                include $file;
                return;
            }
        } catch (ErrorException $e) {
        }
    }
});

/** Turn on all errors so we can catch exceptions. */
error_reporting(E_ALL & ~E_STRICT);

/**
 * Define the generic error handler. All errors in  */
set_error_handler(
    function($errno, $errstr, $errfile, $errline, $errcontext) {
        throw new ErrorException($errstr, 0, $errno, $errfile, $errline);
    },
    error_reporting()
);

/** Required to make PHP handle UTF8 in a sane way. */
if (function_exists('mb_internal_encoding')) {
    mb_internal_encoding('UTF-8');
}
if (function_exists('mb_detect_order')) {
    mb_detect_order([
        'CP1251', 'CP1252', 'ISO-8859-1', 'UTF-8',
    ]);
}

