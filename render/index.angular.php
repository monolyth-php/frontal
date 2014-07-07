<?php namespace monolyth ?>
<!doctype html>
<!--[if lt IE 7]><html ng-controller="MonolythController" class="no-js lt-ie10 lt-ie9 lt-ie8 lt-ie7" lang="{{Site.language.current.code}}"><![endif]-->
<!--[if IE 7]><html ng-controller="MonolythController" class="no-js lt-ie10 lt-ie9 lt-ie8" lang="{{Site.language.current.code}}"><![endif]-->
<!--[if IE 8]><html ng-controller="MonolythController" class="no-js lt-ie10 lt-ie9" lang="{{Site.language.current.code}}"><![endif]-->
<!--[if IE 9]><html ng-controller="MonolythController" class="no-js lt-ie10" lang="{{Site.language.current.code}}"><![endif]-->
<!--[if gt IE 9]><!--><html ng-controller="MonolythController" class="no-js" lang="{{Site.language.current.code}}"><!--<![endif]-->
    <head>
        <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
        <title ng-bind-template="{{Page.title + Page.separator + Site.title}}">[...loading...]</title>
        <meta name="generator" content="Monolyth 5.0.1">
        <meta ng-if="Page.meta.keywords" name="keywords" content="{{Page.meta.keywords.join(', ')}}">
        <meta ng-if="Page.meta.description" name="description" content="{{Page.meta.description}}">
        <meta ng-if="Site.mobileOptimized" name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1">
        <?=$Css?>
<?php if ($project['favicons']) foreach ($project['favicons'] as $favicon) { ?>
        <link rel="icon" ng-type="image/<?=$favicon['type']?>" href="<?=$favicon['href']?>">
<?php } ?>
<?php

try {
    echo $view('\slice/head');
} catch (render\FileNotFound_Exception $e) {
}

?>
    </head>
    <?php require_once $body ?>
</html>

