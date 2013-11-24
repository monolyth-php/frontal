<?php

/**
 * A simple mixin for CSS3 background gradients.
 * This only supports linear gradients in two colours. If you need anything
 * more complex, you'll need to roll your own :)
 */
return function($from, $to) {
    return <<<EOT
background: $to;
background: -moz-linear-gradient(top, $from, $to 100%);
background: -webkit-gradient(linear, left top, left bottom, color-stop(0%, $from), color-stop(100%, $to));
background: -webkit-linear-gradient(top, $from 0%, $to 100%);
background: -o-linear-gradient(top, $from 0%, $to 100%);
background: -ms-linear-gradient(top, $from 0%, $to 100%);
background: linear-gradient(to bottom, $from 0%, $to 100%);
-ms-filter: "progid:DXImageTransform.Microsoft.gradient(startColorStr='$from', endColorStr='$to', GradientType=0)";
filter: progid:DXImageTransform.Microsoft.gradient(startColorstr='$from', endColorstr='$to',GradientType=0);
EOT;
};

