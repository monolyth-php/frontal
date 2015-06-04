<?php

/**
 * The default 404 page.
 *
 * @package monolyth
 * @author Marijn Ophorst <marijn@monomelodies.nl>
 * @copyright MonoMelodies 2008, 2009, 2010, 2011
 */

namespace monolyth\render;
$title = $text('monolyth\render\http404/title');

?>
<article>
    <header><h1><?php echo $title ?></h1></header>
    <?php echo $text('monolyth\render\http404/content') ?>
</article>
<?php return compact('title');

