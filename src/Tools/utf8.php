<?php

/**
 * Force PHP to handle UTF-8 in a sane way (since PHP6 was, you know,
 * cancelled).
 *
 * @package Monolyth
 * @subpackage Tools;
 * @author Marijn Ophorst <marijn@monomelodies.nl>
 * @copyright MonoMelodies 2008, 2009, 2010, 2011, 2012, 2014, 2015
 */
namespace Monolyth\Tools;

/**
 * Required to make PHP handle UTF8 in a sane way. To use, simply include
 * somewhere early in your front controller (typically index.php).
 */
if (function_exists('mb_internal_encoding')) {
    mb_internal_encoding('UTF-8');
}
if (function_exists('mb_detect_order')) {
    mb_detect_order([
        'CP1251', 'CP1252', 'ISO-8859-1', 'UTF-8',
    ]);
}

