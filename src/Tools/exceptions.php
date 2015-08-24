<?php

/**
 * Let all errors throw an exception.
 *
 * @package Monolyth
 * @subpackage Tools
 * @author Marijn Ophorst <marijn@monomelodies.nl>
 * @copyright MonoMelodies 2008, 2009, 2010, 2011, 2012, 2014, 2015
 */
namespace Monolyth\Tools;

/**
 * Sometimes it can be handy to convert all "traditional" PHP errors to
 * exceptions. Simply include this file and from then on, 't shall be done.
 *
 * Caution: not every external library will expect this behaviour, so be
 * prepared to break stuff here and there when using this.
 */
use ErrorException;

/**
 * Turn on all errors so we can catch exceptions.
 */
error_reporting(E_ALL & ~E_STRICT);
/**
 * Define the generic error handler.
 */
set_error_handler(
    function($errno, $errstr, $errfile, $errline, $errcontext) {
        throw new ErrorException($errstr, 0, $errno, $errfile, $errline);
    },
    error_reporting()
);

