<?php namespace monolyth\render\form ?>
<div class="timewidget">
<?php

$e = $o->getElements();
$render = [$e['h'], $e['i'], $e['s']];
echo implode(':', $render);

?>
</div>

