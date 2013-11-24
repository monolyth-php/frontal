<?php

namespace monolyth\render\form;
use monolyth\DependencyContainer;

$container->using(__NAMESPACE__, function() use($container) {
    $container->register(
        'Select',
        ['_Option' => function() { return new Option; }]
    );
    $container->register(
        'Radios',
        ['_Radio' => function() { return new Radio; }]
    );
    $container->register(
        'Checkboxes',
        [
            '_Hidden' => function() { return new Hidden; },
            '_Checkbox' => function() { return new Checkbox; },
        ]
    );
});

