<?php

return function($how) {
    return <<<EOT
-moz-border-radius: $how;
-webkit-border-radius: $how;
border-radius: $how;
EOT;
};

