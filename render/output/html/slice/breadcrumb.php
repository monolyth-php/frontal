<?php namespace monolyth\render ?>

<div class="breadcrumbs">

<?php foreach ($breadcrumbs as $url => $txt) { ?> 
    <?php $arrow = end(array_reverse($breadcrumbs)) == $txt ? '' : '&gt;'; ?>
    <?= end($breadcrumbs) == $txt ? " $arrow $txt " : " $arrow <a href=\"$url\">$txt</a>"; ?> 
<?php } ?>

</div>
