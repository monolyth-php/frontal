<?php

call_user_func(function($dir) {
    require_once 'monolyth/Monolyth.php';
    $project = new Project();
    $project['public'] = $dir;
    Monolyth::run($project);
}, realpath(__DIR__));

