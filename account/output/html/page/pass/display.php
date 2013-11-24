<?php

namespace monolyth\account;
$title = $text('./title');

?>
<article>
    <h1><?=$title?></h1>
    <?=$text('./display', compact('pass'))?>
</article>
<?php return compact('title');

