<?php

/**
 * The default 500 page.
 *
 * @package monolyth
 * @subpackage render
 * @author Marijn Ophorst <marijn@monomelodies.nl>
 * @copyright MonoMelodies 2012
 */

namespace monolyth\render;
$title = $text('monolyth\render\http500/title');

?>
<article>
    <h1><?=$title?></h1>
    <?=$text('monolyth\render\http500/content')?>
</article>
<?php return compact('title');

