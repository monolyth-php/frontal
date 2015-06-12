# Routing
Monolyth uses the [Reroute HTTP router](http://reroute.monomelodies.nl).
For small projects, using a single routing file is fine. Larger projects can
also split them up - this is fine as long as they all use the same
`Reroute\Router` object.

```php
<?php

use Reroute\Router;
use Reroute\Url\Flat;

$router = new Router;

$router->state('foo', new Flat('/foo/', ['GET', 'POST']), function ($VERB) {
    if ($VERB == 'POST') {
        $controller = new Controller;
        $controller->doSomething();
    }
    return new View;
});

```

The exact way Reroute works is explained [in its documentation](http://reroute.monomelodies.nl/docs/),
but the summary is the above code creates an example route ("state" in Reroute
speak) that handles a `GET` or a `POST` to the URL `/foo/`.

If the action was `POST`, we instantiate an object of the `Controller` class
and perform an action.

Finally, we instantiate an object the `View` class and return it. The view is
then rendered.

We'll get to the controller later, first let's look at the view.

