<?php

namespace monolyth\admin;

return function($m) use($project) {
    $m->setDefaultDomain($project['https']);
    $m->connect(
        '/monad/(%s:language)/(%s:package)/(%s:target)/(%s:database)/',
        'monolyth\admin\Media_List_Controller',
        null,
        ['package' => 'monolyth', 'target' => 'media']
    );
    $m->setDefaultDomain($project['http']);
    return $m;
};

