<?php

namespace monolyth\render;
use StdClass;

$bin = realpath(__DIR__.'/../../../../bin/').'/compact';
$desc = [
    0 => ['pipe', 'r'],
    1 => ['pipe', 'w'],
];
$p = proc_open($bin, $desc, $pipes);
fwrite($pipes[0], file_get_contents('monolyth/output/js/head.js', true));
fclose($pipes[0]);
$script = stream_get_contents($pipes[1]);
fclose($pipes[1]);
if (isset($language)) {
    $lang = new StdClass;
    $lang->code = $language->current->code;
    $lang->title = $language->current->title;
    $script = str_replace(
        ['{$HTTPIMG}', '{$LANGUAGE}'],
        [$httpimg(''), json_encode($lang)],
        $script
    );
}

?>
<script><?=$script?></script>
<?php

$return = proc_close($p);

