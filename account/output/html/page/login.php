<?php

/**
 * @package monolyth
 * @subpackage account
 */

namespace monolyth\account;
echo $view(
    ['monolyth\render\form\slice/table', 'monolyth\render\form\slice/form'],
    compact('form')
);

return ['title' => $text('./title')];

