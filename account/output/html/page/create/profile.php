<?php

namespace monolyth\account;

echo $view(
    ['monolyth\render\form\slice/table', 'monolyth\render\form\slice/form'],
    compact('form', 'self')
);
return ['title' => $text('./title')];

