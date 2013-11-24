<?php

namespace monolyth\render\form;

if ($o->isDisabled()) {
    $values = [];
    foreach ($o->getChoices() as $c) {
        if ($c instanceof Checkbox && $c->isChecked()) {
            $values[] = strip_tags($c->getLabel());
        }
    }
    echo implode(', ', $values);
} else {
    echo '<div class="checkboxes bitflags'.(
        $o->isRequired() ? ' monolyth-required' : ''
    ).'">'."\n";
    if ($o->isRequired()) {
    }
    foreach ($o->getChoices() as $c) {
        echo "<div>$c</div>\n";
    }
    echo "</div>\n";
}

