<?php

/**
 * The default redirect page template.
 *
 * MonoLyth by default tries to redirect in three possible ways:
 * - via a 30x header
 * - via a meta-refresh
 * - via JavaScript window.location = ...
 * If all else fails, the user is presented with a 'click here' link.
 *
 * @package monolyth
 */

namespace monolyth;

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHMTL 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" lang="en" xml:lang="en">
<head>
<meta http-equiv="refresh" content="0;url=<?=$url?>"/>
<style type="text/css" media="screen"><!--
body {
    margin: 1em;
    text-align: center;
    font: 62.5% "Verdana", "Arial", sans-serif;
    text-align: center;
    color: #000;
    background: #ccc;
    }
a {
    text-decoration: underline;
    }
a:hover {
    text-decoration: none;
    }
--></style>
</head>
<body onload="window.location.href = '<?=$url?>'">
<script>window.onload = function() { window.location.href = '<?=$url?>'; }</script>
<h1><?=$text('./title')?></h1>
<p>
    <?=$text('./intro')?>
</p>
<p>
    <?=$text('./redirect', $url)?>
</p>
</body>
</html>

