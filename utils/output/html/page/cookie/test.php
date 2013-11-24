<?php

namespace monolyth\utils;
header("Content-type: application/javascript", true);

?>
window.Monolyth.cookies().set(<?=isset($_COOKIE['mocote']) ? 'true' : 'false'
    ?>, <?=isset($_COOKIE['mocook']) ? max((int)$_COOKIE['mocook'], 1) : 1
    ?>, <?=isset($_COOKIE['mocoqu']) ? 'true' : 'false'?>);

