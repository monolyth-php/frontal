<?php

namespace monolyth\account;
$title = $text('./title');

?>
<section>
    <h1><?=$title?></h1>
<?php

echo $text('./explain');
echo $view(
    ['monolyth\render\form\simple', 'monolyth\render\form\form'],
    compact('form')
);

?>
</section>
<?php return compact('title');

