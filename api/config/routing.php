<?php

namespace monolyth\api;

return function($m) use($project) {
    $m->connect('/~/monolyth/view/(%s:language)/(%a:viewname).html', 'monolyth\api\View');
    $m->connect('/~/monolyth/form/(%s:language)/(%a:name)/', 'monolyth\api\Form');
    $m->connect('/~/monolyth/session/', 'monolyth\api\Session');
    $m->connect('/~/monolyth/account/login/', 'monolyth\api\Login');
    $m->connect('/~/monolyth/account/activate/', 'monolyth\api\Activate');
    $m->connect('/~/monolyth/account/email/update/', 'monolyth\api\Update_Email');
    $m->connect('/~/monolyth/account/pass/update/', 'monolyth\api\Update_Pass');
    $m->connect('/~/monolyth/account/pass/reset/', 'monolyth\api\Reset_Pass');
    $m->connect(
        '/~/monolyth/account/activate/request/',
        'monolyth\api\Request_Activate'
    );
    return $m;
};

