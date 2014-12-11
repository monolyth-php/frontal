<?php namespace monolyth\render\form ?>
<div class="media">
    <iframe src="<?=$url(
        'monolyth/render/edit_media',
        ['id' => $o->value ? $o->value : 0]
    )?>" scrolling="no"<?=$o->value ?
        '' :
        ' class="no-img"'?> id="<?=$o->getId()?>" name="<?=$o->getName()?>" frameborder="0"></iframe>
    <label for="delete_<?=$o->getId()?>">
        <input type="checkbox" name="delete_<?=$o->getName()?>" id="delete_<?=$o->getId()?>">
        <?=$text('./media/delete')?>
    </label>
</div>

