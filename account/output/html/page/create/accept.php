<?php

namespace monolyth\account;

echo $self->view(
    ['monolyth\render\form\table', 'monolyth\render\form\form'],
    compact('form')
);

