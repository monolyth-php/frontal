<?php

namespace monolyth\core;
use monolyth\DependencyContainer;
use monolyth\account\Auto_Login_Model;
use monolyth\render\form\Bitflags;
use monolyth\render\form\Checkboxes;
use monolyth\render\form\Checkbox;
use monolyth\render\form\Currency;
use monolyth\render\form\Date;
use monolyth\render\form\Datetime;
use monolyth\render\form\Email;
use monolyth\render\form\File;
use monolyth\render\form\Hidden;
use monolyth\render\form\Info;
use monolyth\render\form\Media;
use monolyth\render\form\Numeric;
use monolyth\render\form\Password;
use monolyth\render\form\Radio;
use monolyth\render\form\Radios;
use monolyth\render\form\Search;
use monolyth\render\form\Select;
use monolyth\render\form\Serial;
use monolyth\render\form\Tel;
use monolyth\render\form\Textarea;
use monolyth\render\form\TextHTML;
use monolyth\render\form\Text;
use monolyth\render\form\Time;
use monolyth\render\form\Url;
use monolyth\render\form\Label;

$container->using(__NAMESPACE__, function() use($container) {
    $container->register(
        'Controller',
        ['autologin' => function($o) { return new Auto_Login_Model; }]
    );
    $container->register(
        'Form',
        [
            '_Bitflags' => function() { return new Bitflags; },
            '_Checkboxes' => function() { return new Checkboxes; },
            '_Checkbox' => function() { return new Checkbox; },
            '_Currency' => function() { return new Currency; },
            '_Date' => function() { return new Date; },
            '_Datetime' => function() { return new Datetime; },
            '_Email' => function() { return new Email; },
            '_File' => function() { return new File; },
            '_Hidden' => function() { return new Hidden; },
            '_Info' => function() { return new Info; },
            '_Media' => function() { return new Media; },
            '_Numeric' => function() { return new Numeric; },
            '_Password' => function() { return new Password; },
            '_Radio' => function() { return new Radio; },
            '_Radios' => function() { return new Radios; },
            '_Search' => function() { return new Search; },
            '_Select' => function() { return new Select; },
            '_Serial' => function() { return new Serial; },
            '_Tel' => function() { return new Tel; },
            '_Textarea' => function() { return new Textarea; },
            '_TextHTML' => function() { return new TextHTML; },
            '_Text' => function() { return new Text; },
            '_Time' => function() { return new Time; },
            '_Url' => function() { return new Url; },
        ] + compact('language'),
        function($form) { return $form->prepare(); }
    );
    $container->register(
        'Element',
        [
            '_Label' => function() { return new Label; },
            'selfClosing' => false,
            'expandAttributes' => false,
        ]
    );
});

