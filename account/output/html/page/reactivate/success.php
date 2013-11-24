<?php

namespace monolyth\account;
$title = $text(__NAMESPACE__.'\activate/title');

?>
<article>
    <header><h1><?=$title?></h1></header>
    <div>
        <?=$text('activate/success/body')?>
    </div>
</article>
<?php return compact('title');

