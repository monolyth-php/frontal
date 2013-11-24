<?php

namespace monolyth\account;
$title = $text('./title');

?>
<article>
    <h1><?=$title?></h1>
    <p><?=$text('./success/explain')?></p>
</article>
<?php return compact('title');

