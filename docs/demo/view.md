# Views
Monolyth uses the [Improse view package](http://improse.monomelodies.nl), which
offers a standardised wrapper for views.

By default Monolyth also uses [Twig](http://twig.sensiolabs.org) for template
rendering, so we offer a view for that. Extend it for you project's own views:

```php
<?php

use Monolyth\TwigView;

class View extends TwigView
{
    protected $template = 'src/example.html.twig';

    public function __invoke()
    {
        $data = [
            'foo' => 'bar',
        ];
        return parent::__invoke($data);
    }
}

```

The exact way Improse works is explained [in its
documentation](http://improse.monomelodies.nl/docs/). The docs also contain more
information on why we think Improse is necessary and why most frameworks get
this part of MVC utterly wrong.

The `TwigView` expects its protected `$template` member to be the name of the
Twig template to render, and passes the `$viewdata` argument to `__invoke` as
variables. So, the above example would give you a `{{ foo }}` variable in the
template `src/example.html.twig` with value `bar`.

> Note that the only assumption the view makes about your directory structure
> is that the "root path" is one folder above the vendor folder the Monolyth
> package is in. I.e., for `/foo/bar/vendor/monomelodies/monolyth` it would
> resolve to `/foo/bar`.

