<?php

namespace monolyth\account;

echo $view(
    ['monolyth\render\form\table', 'monolyth\render\form\form'],
    compact('form')
);
return ['title' => $text('./title')];

