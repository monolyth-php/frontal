<?php

return function($how) {
    $how = implode(', ', func_get_args());
    return <<<EOT
-moz-transition: $how;
-webkit-transition: $how;
transition: $how;
EOT;
};

