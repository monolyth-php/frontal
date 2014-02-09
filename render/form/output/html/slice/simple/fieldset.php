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
<?php   if (!is_numeric($legend) && $legend) { ?>
    <legend><?=$legend?></legend>
<?php

    }
    foreach ($fields as $field) {
        if (is_null($field)) {
            continue;
        }
        if ($field instanceof Info) {
            echo $field->value;
            continue;
        }
        if ($field instanceof Radios || $field instanceof Checkboxes) {

?>
    <div class="<?php echo $field instanceof Radios ? 'radio' : 'checkbox';
    if ($field->isRequired()) { echo ' required'; } ?>">
        <?=$field->getLabel()?>
        <?=$field?>
    </div>
<?php       } elseif ($field instanceof Radio || $field instanceof Checkbox) { ?>
    <div class="<?=$field instanceof Radios ? 'radio' : 'checkbox'?>">
        <?=$field?>
    </div>
<?php       } elseif (is_array($field) || !($field instanceof Textarea)) { ?>
    <div>
        <?=is_array($field) ? $field[0]->getLabel() : $field->getLabel()?>
        <?php

            if (!is_array($field)) {
                $field = [$field];
            }
            foreach ($field as $i => $sf) {
                if ($i) {
                    echo $sf->getLabel();
                }
                echo $sf;
            }

?></div>
<?php       } else { ?>
    <div>
        <?=$field->getLabel()?>
        <?=$field?>
    </div>
<?php

        }
    }

?>
</fieldset>

