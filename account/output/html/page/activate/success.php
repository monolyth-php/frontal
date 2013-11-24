<?php

namespace monolyth\account;
$title = $self->text(__NAMESPACE__.'\activate/title');

?>
<section>
    <header><h1><?php echo $title ?></h1></header>
    <div>
        <?php echo $self->text('activate/success/body') ?>
    </div>
</section>
<?php return compact('title');

