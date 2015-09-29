<?php

/**
 * This is an example base view you can extend for all your pages. It uses Twig,
 * so you need to set that up correctly in your dependencies.
 */
use Disclosure\Injector;
use Disclosure\Container;
use Improse;

abstract class View extends Improse\View
{
    use Injector;

    protected $twig;
    protected $template;
    
    public function __construct()
    {
        $this->inject(function ($twig) {});
    }
    
    public function __invoke(array $__viewdata = [])
    {
        return $this->twig->render($this->template, $__viewdata);
    }
}

/**
 * Example injection of Twig into your views (we don't want to make any
 * assumptions about your path structure etc.:
 */
/*
View::inject(function (&$twig) {
    $loader = new Twig_Loader_Filesystem('/path/to/code');
    $twig = new Twig_Environment($loader, [
        'cache' => '/path/to/cache',
        'debug' => true|false,
        'auto_reload' => true|false,
    ]);
    
    // Example url function, assuming `$router` is available:
    $url = function ($name, array $args = []) use ($router) {
        if (!isset($_SERVER['SERVER_NAME'])) {
            $_SERVER['SERVER_NAME'] = 'http://localhost';
        }
        return $router->get($name)->url()->short(
            "http://{$_SERVER['SERVER_NAME']}{$_SERVER['REQUEST_URI']}",
            $args
        );
    };
    $twig->addFunction(new Twig_SimpleFunction('url', $url));
    
    // Example integration of Metaculous:
    $twig->addExtension(new Metaculous\TwigExtension(
        $arrayOfIgnoreWords,
        $hashOfRiggedWords
    ));
});
*/

