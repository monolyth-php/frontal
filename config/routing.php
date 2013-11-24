<?php

namespace monolyth;

return function($m) use($project) {
    foreach ([$project['http'], $project['https']] as $domain) {
        $m->setDefaultDomain($domain);
        $m->connect('/media/', 'Tmp_Media');
        $m->connect('/monolyth/cookietest.js', 'monolyth\utils\Test_Cookie');
        $m->connect('/monolyth/cookiestore/', 'monolyth\utils\Store_Cookie');
    }
    $m->setDefaultDomain($project['http']);
    return $m;
};

