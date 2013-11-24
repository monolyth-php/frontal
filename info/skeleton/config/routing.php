<?php

return function($m) use($project) {
    $m = call_user_func(require 'monolyth/config/routing.php', $m);
    /**
     * Uncomment the following line to include Monad admin routing.
     */
    //$m = call_user_func(require 'monad/admin/config/routing.php', $m);
    $m->connect('/', '');
    return $m;
};

