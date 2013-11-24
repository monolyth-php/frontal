<?php

namespace monolyth\account;

echo $self->view(
    ['monolyth\render\form\slice/table', 'monolyth\render\form\slice/form'],
    compact('form')
);

