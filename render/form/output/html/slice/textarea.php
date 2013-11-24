<?php namespace monolyth\render\form ?>
<textarea <?=$o->renderOptions()?>><?=htmlentities($o->value, ENT_COMPAT, 'UTF-8')?></textarea>

