<?php

namespace monolyth\account;
$title = $text('./error/title');

?>
<article>
    <h1><?=$title?></h1>
    <?=$text(
        './error/explain',
        $url('monolyth/account/activate'),
        $url('monolyth/account/update_email'),
        $user->email()
    )?>
</article>
<?php return compact('title');

