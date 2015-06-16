<?php

use Disclosure\Injector;
use Disclosure\Container;
use Improse\View;

abstract class TwigView extends View
{
    use Injector;

    protected $twig;
    protected $template;
    
    public function __construct()
    {
        $this->inject(function ($adapter, $url, $twig) {});
    }
    
    public function __invoke(array $__viewdata = [])
    {
        return $this->twig->render($this->template, $__viewdata);
    }
}

TwigView::inject(function (&$twig) {
    $base = dirname(dirname(__DIR__));
    $loader = new Twig_Loader_Filesystem($base);
    $twig = new Twig_Environment($loader, [
        'cache' => "$base/.twig-cache",
    ]);
});

