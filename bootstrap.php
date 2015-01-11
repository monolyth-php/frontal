<?php

namespace monolyth;

spl_autoload_register(function($class) {
    $namespaces = explode('\\', $class);
    $class = array_pop($namespaces);

    // All lowercase namespaces are considered a "submodule", which allows
    // us to place submodules in their own repository but still fall under the
    // global namespace of their "parent" module. For example:
    // In the namespace monolyth we have authentication helpers in
    // monolyth\auth. To allow separate cloning and substitution by the same
    // name, we store this in monolyth-auth instead.
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
    $file = sprintf(
        '%s%s%s%s%s.php',
        implode('-', $modules),
        $modules ? DIRECTORY_SEPARATOR : '',
        implode(DIRECTORY_SEPARATOR, $namespaces),
        $namespaces ? DIRECTORY_SEPARATOR : '',
        $class
    );
    // Use fopen so it supports include_path:
    $fp = @fopen($file, 'r', true);
    if ($fp) {
        fclose($fp);
        include $file;
    }
});

