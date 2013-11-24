<?php

namespace monolyth\render\form;

if (!($fieldsets = $form->getFieldsets())) {
    $fieldsets = [$form->getPublicFields()];
}
echo $view(__NAMESPACE__.'\slice/hiddens', compact('form'));
foreach ($fieldsets as $legend => $fields) {
    echo $view(
        __NAMEPSACE__.'\slice/basic/fieldset',
        compact('legend', 'fields')
    );
}
if (!isset($buttons) || $buttons) {
    echo $view(__NAMESPACE__.'\slice/buttons', compact('form'));
}

