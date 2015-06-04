<?php

namespace monolyth\utils;
use monolyth\DependencyContainer;
use monolyth\Text_Model;
use monolyth\HTTP_Model;

$container->using(__NAMESPACE__, function() use($container, $language) {
    $container->register(
        'Translatable',
        [
            'text' => function($o) { return new Text_Model($o); },
            'language' => $language,
        ]
    );
    $container->register(
        [
            'Test_Cookie_Controller',
            'Store_Cookie_Controller',
        ],
        ['cookie' => function() { return new Cookie_Model; }]
    );
    $container->register(
        'Cookie_Model',
        ['http' => function() { return new HTTP_Model; }]
    );
});

