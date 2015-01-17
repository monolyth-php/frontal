<?php

namespace monolyth\render\form;
$has = false;
foreach ($fields as $f) {
    if (!($f instanceof Hidden || is_string($f))) {
        $has = true;
    }
}
if (!$has) {
    return;
}

?>
<fieldset>
<?php if (!is_numeric($legend) and $legend) { ?>
    <legend><?=$legend?></legend>
<?php } ?>
<table<?=isset($class) ? ' class="'.$class.'"' : ''?>>
<?php

foreach ($fields as $f) {
    if ($f instanceof Hidden || is_string($f)) {
        continue;
    }
    $viewname = $form->getView($f);
    if (method_exists($f, 'showInverted') && $f->showInverted()) {
        $viewname .= 'inverse';
    }
    echo $view($viewname, ['row' => $f]);
}

?>
</table>
</fieldset>

