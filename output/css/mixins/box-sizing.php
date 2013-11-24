<?php

return function($how) {
    return <<<EOT
-webkit-box-sizing: $how;
-moz-box-sizing: $how;
-ms-box-sizing: $how;
-o-box-sizing: $how;
box-sizing: $how;
EOT;
};

