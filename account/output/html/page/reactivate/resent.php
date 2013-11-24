<?php

namespace monolyth\account;
$title = $self->text('./title');

?>
<section>
    <header><h1><?php echo $title ?></h1></header>
    <?php echo $self->text('./resent/explain') ?>
</section>
<?php return compact('title');

