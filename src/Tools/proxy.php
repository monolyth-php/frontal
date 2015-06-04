<?php

/**
 * Handle REMOTE_ADDR in Apache when behind a proxy.
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
 * TODO: make this work for other servers besides Apache.
 */
if (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
    $_SERVER['REMOTE_ADDR'] = trim(array_shift(explode(
        ',',
        $_SERVER['HTTP_X_FORWARDED_FOR']
    )));
}

