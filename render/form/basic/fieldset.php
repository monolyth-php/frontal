<?php namespace monolyth\render\form ?>
<fieldset>
<?php   if (!is_numeric($legend) && $legend) { ?>
    <legend><?=$legend?></legend>
<?php

    }
    foreach ($fields as $field) {
        if ($field instanceof Radios || $field instanceof Checkboxes) {

?>
    <span class="<?php echo $field instanceof Radios ? 'radio' : 'checkbox';
    if ($field->isRequired()) { echo ' required'; } ?>">
        <?=$field->getLabel()?>
        <?=$field?>
    </span>
<?php       } elseif ($field instanceof Radio
                || $field instanceof Checkbox) { ?>
    <span class="<?=$field instanceof Radios ? 'radio' : 'checkbox'?>">
        <?=$field?>
    </span>
<?php       } elseif (is_array($field) || !($field instanceof Textarea)) { ?>
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
        } else { ?>
        <?=$field->getLabel()?>
        <?=$field?>
<?php

        }
    }

?>
</fieldset>

