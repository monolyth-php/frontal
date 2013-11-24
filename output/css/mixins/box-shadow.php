<?php

return function($how) {
    return <<<EOT
-moz-box-shadow: $how;
-webkit-box-shadow: $how;
box-shadow: $how;
EOT;
};

