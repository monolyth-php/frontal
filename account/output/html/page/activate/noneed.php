<?php

namespace monolyth\account;
$title = $self->text('./noneed/title');

?>
<section>
    <h1><?php echo $title ?></h1>
    <?php echo $self->text('./noneed/explain') ?>
</section>
<?php return compact('title');

