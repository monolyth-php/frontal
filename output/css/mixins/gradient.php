<?php

/**
 * A mixin for CSS3 background gradients.
 * There is only one required argument: $fallback, being your desired
 * fallback colour if gradients are unsupported in the current browser.
 * The optional second argument can be either 'vertical' (top-down) or
 * 'horizontal' (left-right), defaulting to 'vertical'.
 * All subsequent arguments are of the form 'color:stop'.
 * Older internet explorers get a guesstimated simple gradient between
 * the lightest and darkest colour supplied (based on whichever comes first).
 */
return function($fallback, $type = 'vertical') {
    $args = func_get_args();
    $fallback = array_shift($args);
    $type = trim(array_shift($args));
    $default_vendor_prefixed = function($prefix, $type, $stops) {
        $out = "background: -$prefix-linear-gradient(";
        if ($type == 'vertical') {
            $out .= 'top';
        } else {
            $out .= 'left';
        }
        foreach ($stops as $arg) {
            list($colour, $stop) = explode(':', $arg);
            $out .= ", $colour $stop";
        }
        $out .= ");\n";
        return $out;
    };

    $out = '';
    foreach (['moz', 'o', 'ms'] as $prefix) {
        $out .= $default_vendor_prefixed($prefix, $type, $args);
    }   
    $out .= "background: -webkit-gradient(linear, ";
    if ($type == 'vertical') {
        $out .= "left top, left bottom";
    } else {
        $out .= "left top, right top";
    }
    foreach ($args as $arg) {
        list($colour, $stop) = explode(':', $arg);
        $out .= ", color-stop($stop, $colour)";
    }
    $out .= ");\n";
    $out .= "background: linear-gradient(";
    if ($type == 'vertical') {
        $out .= "to bottom";
    } else {
        $out .= "90deg";
    }
    $out .= ", ";
    foreach ($args as $arg) {
        list($colour, $stop) = explode(':', $arg);
        $out .= ", $colour $stop";
    }
    $out .= <<<EOT
);
background-color: $fallback;

EOT;
    $start = explode(':', array_shift($args))[0];
    $end = explode(':', array_pop($args))[0];
    $type = $type == 'vertical' ? 0 : 1;
    $out .= <<<EOT
-ms-filter: "progid:DXImageTransform.Microsoft.gradient(startColorStr='$start', endColorStr='$end', GradientType=0)";
filter: progid:DXImageTransform.Microsoft.gradient(startColorstr='$start', endColorstr='$end',GradientType=0);

EOT;
    return $out;
};

