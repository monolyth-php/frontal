<?php

return function($how) {
    return <<<EOT
-moz-text-shadow: $how;
-webkit-text-shadow: $how;
text-shadow: $how;
EOT;
};

