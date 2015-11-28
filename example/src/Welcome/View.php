<?php

/**
 * Example view extending the base view.
 */

namespace Welcome;

class View extends \View
{
    /**
     * This should contain the name of the Twig view you want to render this
     * View with. It's relative to Twig's template path(s).
     */
    protected $template = 'Welcome/template.html.twig';

    /**
     * Constructor. You can extend this to load custom data, e.g.
     * from your database.
     */
    public function __construct()
    {
        // You'll want to call this to setup Twig or whatever you use in
        // the parent base View.
        parent::__construct();
    }

    /**
     * Invoker. You can extend this for custom functionality, e.g. register
     * the data loaded in the constructor on the $data hash. Unless you're
     * really sure you need an override, forward that to `parent::__invoke`.
     */
    public function __invoke(array $data = [])
    {
        return parent::__invoke($data);
    }
}

