<?php

/**
 * The Monolyth render dependencies. Generally, you'll want these (unless
 * you're writing tests or something).
 *
 * @package monolyth
 * @subpackage render
 * @author Marijn Ophorst <marijn@monomelodies.nl>
 * @copyright MonoMelodies 2012, 2013
 */

namespace monolyth\render;
use monolyth\utils\HTML_Helper;
use Mail;
use Mail_Mime;
use monolyth\adapter\Logger;
use monolyth\DependencyContainer;

$container->using(__NAMESPACE__, function() use($container) {
    $container->register(
        'Url_Helper',
        compact('language', 'router')
    );
    $container->register(
        'Email',
        [
            'parser' => function() { return new Translate_Parser; },
            'mail' => function() {
                /**
                 * @see PEAR::Mail
                 */
                require_once 'Mail.php';
                /** @see PEAR::Mail_mime */
                require_once 'Mail/mime.php';
                return new Mail_mime([
                    'head_charset' => 'UTF-8',
                    'html_charset' => 'UTF-8',
                    'text_charset' => 'UTF-8',
                    'html_encoding' => 'quoted-printable',
                    'text_encoding' => 'quoted-printable',
                    'eol' => "\n",
                ]);
            },
            'send' => function() { return Mail::factory('mail'); },
        ]
    );
    $container->register(
        'View',
        ['logger' => function() { return new Logger; }]
    );
    $container->register(
        'Menu',
        ['item' => function() { return new Item_Menu; }]
    );
    $container->register(
        'Edit_Media_Controller',
        ['form' => function() { return new Edit_Media_Form; }]
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
    $container->register(
        'Media_Controller',
        ['medias' => function() { return new Media_Finder; }]
    );
    $container->register(
        'Media_Parser',
        ['media' => function() { return new Media_Helper; }]
    );
    $container->register(
        'Gettext_Controller',
        ['translate' => function() { return new Script_Translate_Parser; }]
    );
});
require 'monolyth/render/form/config/dependencies.php';

