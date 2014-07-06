<?php

namespace monolyth\api;

return function($m) use($project) {
    $m->connect('/monolyth/form/(%s:language)/(%a:name)/', 'monolyth\api\Form');
    return $m;
};

