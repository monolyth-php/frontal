<?php

/**
 * This is an example base view you can extend for all your pages. It uses Twig,
 * so you need to set that up correctly in your dependencies. See
 * `./src/dependencies.php` for an example.
 */
use Disclosure\Injector;

abstract class View extends Improse\View
{
    use Injector;

    protected $twig;
    protected $template;
    
    public function __construct()
    {
        /**
         * This uses the Disclosure Injector to inject $twig into a member.
         * See the Disclosure documentation for more information.
         */
        $this->inject(function ($twig) {});
    }
    
    public function __invoke(array $__viewdata = [])
    {
        return $this->twig->render($this->template, $__viewdata);
    }
}

