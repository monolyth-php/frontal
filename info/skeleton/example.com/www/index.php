<?php

call_user_func(function($dir) {
    require_once 'monolyth/Monolyth.php';
    $project = Project::instance();
    $project['public'] = $dir;
    Monolyth::run($project);
}, realpath(__DIR__));

