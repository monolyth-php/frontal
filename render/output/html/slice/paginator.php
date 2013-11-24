<?php namespace monolyth\render ?>
<nav class="paginator">
    <ul>
<?php for ($i = $paginator->first(); $i <= $paginator->last(); $i++) { ?>
        <li<?=$i == $paginator->current() ? ' class="current"' : ''
            ?>><a href="<?=$paginator->url(['page' => $i])?>"><?=$i?></a></li>
<?php } ?>
    </ul>
</nav>
