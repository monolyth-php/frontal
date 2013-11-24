<?php

/**
 * The default Monolyth dependencies. Generally, you'll want these (unless
 * you're writing tests or something).
 *
 * @package monolyth
 * @author Marijn Ophorst <marijn@monomelodies.nl>
 * @copyright MonoMelodies 2011, 2012, 2013
 */

namespace monolyth;

$container->using(__NAMESPACE__, function() use($container) {
    $container->register(
        '',
        ['config' => function() { return Config::get('monolyth'); }]
    );
    $http = new HTTP_Model;
    $container->register(
        'HTTP_Model',
        ['link' => function() { return new utils\Link; }]
    );
    $container->register('HTTP_Access', compact('http'));
    $container->register(
        'core\Session_Model',
        ['http' => $http],
        function($session) {
            static $inited = false;
            if (!$inited) {
                $session->init();
                $inited = true;
            }
            return $session;
        }
    );
    $container->register(
        'ACL',
        ['resourceModel' => function() { return new ACLResource_Model; }],
        function($acl) {
            $acl->init();
            return $acl;
        }
    );
    /**
     * Override this after loading Monolyth dependencies to use different
     * types of session.
     */
    $container->register(
        'Session_Access',
        ['session' => function() { return new PHP_Session_Model; }]
    );
    /**
     * Override this after loading Monolyth dependencies to use caching,
     * usually using Memcached (though other schemes are perfectly possible).
     */
    $container->register(
        'Cache_Access',
        ['cache' => function() { return new adapter\nosql\Dummy; }]
    );

    $container->register(
        'User_Access',
        ['user' => function() { return new User_Model; }]
    );
    $container->register(
        'Country_Access',
        ['countries' => function() { return new Country_Finder; }]
    );
    $container->register(
        'Message_Access',
        ['message' => function() { return new Message; }]
    );
    $container->register(
        'Message',
        ['dummy' => true],
        function($message) {
            $message->init();
            return $message;
        }
    );
    $container->register(
        'User_Model',
        [
            'acl' => function($o) { return new ACL_Model($o); },
            'login' => function() { return new account\Login_Model; },
            'logout' => function() { return new account\Logout_Model; },
        ],
        function($user) {
            $user->acl->init();
            return $user;
        }
    );
    $container->register(
        'Comment_Finder',
        ['comment' => function() { return new Comment_Model; }]
    );
    return $container;
});
require 'monolyth/core/config/dependencies.php';
require 'monolyth/utils/config/dependencies.php';
require 'monolyth/render/config/dependencies.php';
require 'monolyth/account/config/dependencies.php';

