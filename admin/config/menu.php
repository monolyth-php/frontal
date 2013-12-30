<?php

namespace monolyth\admin;

$menu->using(__NAMESPACE__, function() use($menu) {
    if ($menu->user->inGroup('Administrators')) {
        $menu->group('settings')->add('language')
                                ->add('text')
                                ->add('mail')
                                ->add('mail_template');
    }
});

