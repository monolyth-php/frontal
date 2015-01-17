<?php

namespace monolyth\utils;

interface Array_Translatable
{
    public function translateKeys(array $array);
    public function translateValues(array $array);
}

