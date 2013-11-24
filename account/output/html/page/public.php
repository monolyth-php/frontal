<?php

namespace monolyth\account;
$title = $self->text('./title', $profile['name']);

?>
<article>
    <h1><?php echo $title ?></h1>
    <dl>
<?php foreach ($profile as $key => $value) { ?>
        <dt><?php echo $self->text("./$key") ?></dt>
        <dd><?php echo $value ?></dd>
<?php } ?>
    </dl>
</article>
<?php return compact('title');

