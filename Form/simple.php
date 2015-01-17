<?php

namespace monolyth\Form;

use Improse\Render\Html;

if (!($fieldsets = $form->getFieldsets())) {
    $fieldsets = [$form->getPublicFields()];
}
$hidden = new Html('monolyth/Form/hiddens.php');
$hidden(compact('form'));
foreach ($fieldsets as $legend => $fields) {
    $fieldset = new Html('monolyth/Form/simple/fieldset.php');
    $fieldset(compact('legend', 'fields'));
}
if (!isset($buttons) || $buttons) {
    $buttons = new Html('monolyth/Form/buttons.php');
    $buttons(compact('form'));
}

