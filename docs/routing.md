# Routing
Since routing is what makes an MVC project tick, you'll need to add some routes
so the front controller can do something a bit more useful than showing the
default welcome page.

Exactly how you do your routing and what you use for that is up to you. Monolyth
suggests using [Reroute](http://reroute.monomelodies.nl). An advantage of
Reroute over many other routing solutions is that it's fully compatible with
pipelines. Hence, you can pass it to the front contoller like so:

```php
<?php

use Reroute\Router;

$router = new Router;
// Define routes, see Reroute documentation...

$controller->pipe($router);
$controller->run();
```

It's the job of a router to match the request's URI to code that does something.

More information: [Reroute documentation](http://reroute.monomelodies.nl)

## Alternative routing modules
Let's say you prefer to use [bramus/router](https://packagist.org/packages/bramus/router)
for your routing. To integrate it with Monolyth, you need to perform two steps:

1. Wrap it in a lambda so we can pipe it;
2. Make it return an object implementing `Psr\Http\Message\ResponseInterface`.

An example integration could look as follows:

```php
<?php

use Zend\Diactoros\Response\HtmlResponse;
use Bramus\Router\Router;

$router = new Router;
// Define routes, see Bramus\Router documentation...

// Wrap in a lamba to make it pipeable:
$controller->pipe(function ($request) use ($router) {
    ob_start();
    $router->run();
    return new HtmlResponse(ob_get_clean());
});
$controller->run();
```

> Of course, you can also choose not to use `HttpController` at all and
> implement your own or an alternative package's logic.

