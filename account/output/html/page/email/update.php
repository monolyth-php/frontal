<?php

namespace monolyth\account;
$title = $text('./title');

?>
<article>
    <h1><?=$title?></h1>
    <?=$view(
        ['monolyth\render\form\slice/simple', 'monolyth\render\form\slice/form'],
        compact('form')
    )?>
</article>
<?php return compact('title');

