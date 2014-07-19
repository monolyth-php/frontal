<?php

namespace monolyth\render\form;

if ($btns = $form->getButtons()) {

?>
<div class="buttons">
<?php   foreach ($btns as $btn) { ?>
    <?=$btn?>

<?php   } ?>
    <hr>
</div>
<?php } ?>
