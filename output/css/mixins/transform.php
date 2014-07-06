<?php

return function($how) {
    $how = implode(', ', func_get_args());
    return <<<EOT
-webkit-transform: $how;
-moz-transform: $how;
-ms-transform: $how;
-o-transform: $how;
transform: $how;
EOT;
};

