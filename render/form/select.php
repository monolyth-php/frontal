<?php namespace monolyth\render\form ?>
<select <?=$o->renderOptions()?>>
<?php foreach ($o->getChoices() as $choice) { ?>
    <?=$choice?>

<?php } ?>
</select>

