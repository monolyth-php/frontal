<?php namespace monolyth\render ?>
<body class="edit_media">
<form id="update_media"<?php

if (isset($imagefile)) {
    echo ' style="background-image: url('.$url('monolyth/render/tmp_media').'?f='
        .urlencode($imagefile).'" class="edit"';
}

?> method="post" action="" enctype="multipart/form-data">
<div>
    <input type="hidden" name="MAX_FILE_SIZE" value="<?=$max?>">
    <?=$form['media']?>
    <span><span><?=$text(isset($imagefile) ? './edit' : './add', $position)?></span></span>
</div>
<script src="<?=$httpimg('/js/LAB.js', 'cdn')?>"></script>
<script>
    Monolyth.user = Monolyth.user ? Monolyth.user : {};
    Monolyth.user.loggedIn = <?=$user->loggedIn() ? 'true' : 'false'?>;
    Monolyth.user.login = '<?=$url('monolyth/account/login')?>';
<?php if (isset($imagefile)) { ?>
    window.parent.setImage('<?=$imagefile?>', '<?=$position?>');
<?php } ?>
    $LAB.script('//ajax.googleapis.com/ajax/libs/jquery/1/jquery.min.js').
         wait(function() {
            $LAB.script([<?=$Script?>]).
                 wait(function() {
                    <?=isset($scripts) && $scripts ? implode("\n", $scripts) : ''?>
                 });
            });
</script>
</body>

