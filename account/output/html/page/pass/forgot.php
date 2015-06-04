<?php

namespace monolyth\account;
$title = $text('./title');

?>
<article>
    <h1><?=$title?></h1>
<?=$view(
    ['monolyth\render\form\table', 'monolyth\render\form\form'],
    compact('form')
)?>
</article>
<?php return compact('title');

