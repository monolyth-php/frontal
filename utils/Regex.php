<?php

/**
 * A utility interface defining several handy regexes.
 *
 * @package monolyth
 * @subpackage utils
 * @author Marijn Ophorst <marijn@monomelodies.nl>
 * @copyright 2010, 2012 MonoMelodies
 */

namespace monolyth\utils;

interface Regex
{
    const REGEX_DOMAIN =
        "/^[a-zA-Z0-9][-a-zA-Z0-9.]+\.[a-zA-Z]{2,4}$/";
    const REGEX_URI =
        "/^(http:\/\/)?(www.)?[a-zA-Z0-9][-a-zA-Z0-9.]+\.[a-zA-Z]{2,4}$/";
    const REGEX_EMAIL =
        "/^[-_a-zA-Z0-9.]+@[a-zA-Z0-9][-a-zA-Z0-9.]+\.[a-zA-Z]{2,4}$/";
}

