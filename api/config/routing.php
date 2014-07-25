<?php

namespace monolyth\api;

return function($m) use($project) {
    $m->connect('/html/(%s:language)/(%a:viewname).html', 'monolyth\api\View');
    $m->connect('/monolyth/form/(%s:language)/(%a:name)/', 'monolyth\api\Form');
    $m->connect('/monolyth/session/', 'monolyth\api\Session');
    $m->connect('/monolyth/account/login/', 'monolyth\api\Login');
    $m->connect('/monolyth/account/activate/', 'monolyth\api\Activate');
    return $m;
};

