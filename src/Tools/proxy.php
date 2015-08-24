<?php

/**
 * Handle REMOTE_ADDR in when behind a proxy.
 *
 * @package Monolyth
 * @subpackage Tools
 * @author Marijn Ophorst <marijn@monomelodies.nl>
 * @copyright MonoMelodies 2008, 2009, 2010, 2011, 2012, 2014, 2015
 */
namespace Monolyth\Tools;

/**
 * Correct REMOTE_ADDR if we're behind a proxy.
 * This code is by no means extensive; there's prolly 1.000 other
 * cases you'll want to handle.
 *
 * To use, simply include this somewhere early in your front controller
 * (typically index.php).
 */
if (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
    $_SERVER['REMOTE_ADDR'] = trim(array_shift(explode(
        ',',
        $_SERVER['HTTP_X_FORWARDED_FOR']
    )));
}

