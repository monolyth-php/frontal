<?php

namespace monolyth\account;
use monolyth\DependencyContainer;
use monolyth\User_Finder;
use monolyth\Confirm_Model;
use monolyth\render\Email;
use monolyth\HTTP_Model;

$container->using(__NAMESPACE__, function() use($container) {
    $http = new HTTP_Model;
    $container->register(
        [
            'Controller',
            'Update_Controller',
        ],
        [
            'form' => function() { return new Update_Form; },
        ]
    );
    $container->register(
        'Login_Model',
        compact('http') + ['pass' => function() {
            return new Check_Pass_Model;
        }]
    );
    $container->register(
        'Auto_Login_Model',
        ['form' => function() { return new Login_Form; }]
    );
    $container->register(
        'Login_Controller',
        ['form' => function() { return new Login_Form; }]
    );
    $container->register('Login_Form', compact('http'));
    $container->register(
        'Forgot_Pass_Controller',
        [
            'form' => function() { return new Forgot_Pass_Form; },
            'reset' => function() { return new Reset_Pass_Model; },
        ]
    );
     $container->register(
        'Reset_Pass_Model',
        [
            'confirm' => function() { return new Confirm_Model; },
            'email' => function() { return new Email; },
        ]
    );
    $container->register(
        [
            'Reset_Pass_Controller',
            'Confirm_Pass_Controller',
        ],
        [
            'form' => function() { return new Reset_Pass_Form; },
            'confirm' => function() { return new Confirm_Model; },
            'pass' => function() { return new Pass_Model; },
        ]
    );
    $container->register(
        'Activate_Model',
        [
            'confirm' => function() { return new Confirm_Model; },
            'email' => function() { return new Email; },
        ]
    );
    $container->register(
        [
            'Activate_Controller',
            'Re_Activate_Controller',
            'Request_Re_Activate_Controller',
        ],
        [
            'form' => function() { return new Activate_Form; },
            'activate' => function() { return new Activate_Model; },
        ]
    );
    $container->register(
        'Update_Pass_Controller',
        [
            'form' => function() { return new Update_Pass_Form; },
            'pass' => function() { return new Pass_Model; },
        ]
    );
    $container->register(
        'Update_Controller',
        ['update' => function() { return new Update_Model; }]
    );
    $container->register(
        'Public_Controller',
        ['users' => function() { return new User_Finder; }]
    );
    $container->register(
        'Profile_Create_Form',
        ['account' => function() { return new User_Model; }]
    );
    $container->register(
        'User_Model',
        [
            'pass' => function() { return new Pass_Model; },
            'check' => function() { return new Check_Pass_Model; },
            'activate' => function() { return new Activate_Model; },
        ]
    );
    $container->register(
        'Update_Name_Controller',
        [
            'update' => function() { return new User_Model; },
            'form' => function() { return new Update_Name_Form; },
        ]
    );
    $container->register(
        'Update_Email_Controller',
        [
            'form' => function() { return new Update_Email_Form; },
            'account' => function() { return new User_Model; },
        ]
    );
    $container->register(
        'Delete_Controller',
        [
            'form' => function() { return new Confirm_Delete_Form; },
            'delete' => function() { return new User_Model; },
        ]
    );
    $container->register(
        'Create_Model',
        [
            'account' => function() { return new User_Model; },
            'pass' => function() { return new Pass_Model; },
            'form' => function() { return new Login_Form; },
            'activate' => function() { return new Activate_Model; },
        ]
    );
    $container->register(
        'Create_Controller',
        ['create' => function() { return new Create_Model; }]
    );
    return $container;
});

