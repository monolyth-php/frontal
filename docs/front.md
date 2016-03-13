# The front controller
So you're setup correctly and are looking at our _beautiful_ (ahum) welcome page
in your favourite web browser. Let's look at what's going on here:

## `index.php`
This is your entry point. Open `index.php` in a text editor.

You'll see that it `include`s the Composer autoloader, and does a few generic
operations (essentially fix PHP's handling of UTF-8, and a quick check for sites
behind a proxy - YMMV depending on your server software/proxy brand). It then
instantiates, configures and runs the _front controller_.


## `Http\Controller`
A front controller, in MVC terms, is sort of your central hub where all
non-static requests get funelled to, and that decides what should happen next
(usually based on some routing table).

Monolyth's front controller - the `Http\Controller`, since it will be used to
build a web app - works with _pipelines_. This means that your application is
effectively wrapped in transformers (like an onion skin), each of which forwards
its result to the next one.

For simple apps, you can call `pipe` on the `Http\Controller` object to build
the pipeline. For more complicated apps (or lots of pipes), you could extend the
default `Http\Controller` and use the constructor and/or overridden `run` method
to setup your logic.

The pipeline is initialized by calling `run` and is started with a _request
object_. Monolyth uses a `Zend\Diactoros\ServerRequest` object for this, which
represents the current HTTP request. Somewhere in the pipeline, the returned
`$payload` must become a _response object_. This can be any object implementing
`Psr\Http\Message\ResponseInterface`. It then uses `Diactoros`'s SAPI event
emitter to emit that to the browser.

The example simply returns a welcome page:

```php
<?php

use Zend\Diactoros\Reponse\HtmlResponse;

// ...snip ...

$controller->pipe(function ($request) {
    // Note how the passed in request is a _response_ after this cycle
    // in the pipeline:
    return new HtmlResponse(file_get_contents('../src/template.html'));
});
```

Obviously this is only sufficient for the simplest of sites, but the example
already offers some pointers on how to proceed from here.

## Exception handling
If any exception gets thrown, your front controller should handle that
gracefully (or not so gracefully if you're developing :)). To handle exception
catching, you can either wrap the call to `run` in a `try`/`catch` block (in
which case you'll have to handle your own emitting) or specify the behaviour in
your `run` override in a derived controller.

The example `index.php` uses the first method and just re-throws the exception.
Obviously this is not fit for production code, it's just an example.

For more user-friendly errors you can use e.g. `filp/whoops`, which is a nice
little library that quickly formats your error pages. Here's an example of error
handling:

```php
<?php

use Whoops\Run;
use Whoops\Handler\PrettyPageHandler;

// ...
$whoops = new Whoops\Run;
$whoops->pushHandler(new PrettyPageHandler);
$whoops->register();
try {
    $controller->run();
} catch (Throwable $e) {
    // PHP7 throws an instance of `Throwable`. For PHP5.*, you would catch
    // `Exception` instead.
    if (!($e instanceof Exception)) {
        // Whoops isn't PHP7 compatible, so we fake it:
        throw new Exception($e->getMessage(), $e->getCode(), $e);
    }
}
```

Whoops comes bundled with some other handlers, e.g. `JsonResponseHandler`. It's
up to you to pick the right one for your application.

Obviously the above example should be extended to [check for
environment](http://envy.monomelodies.nl) and only use Whoops for development.
In production mode it should should a friendlier HTTP 500 page.

Monolyth comes with the `Monolyth\Http\Exception` which you can throw in your
own code and inspect in the catch handler. Its `getCode()` method returns the
desired HTTP state, so e.g. a 403 exception could redirect to a login page.

A best practice is to have a generic fallback that gives a HTTP 500 error for
any other uncaught `Throwable`.

Note that if your pipeline resolves to `null`, and empty 404 is emitted by
default. It usually means you forgot something in your pipeline, or a route
isn't matching due to a typo or whatever.

## Manually emitting a response
When `run` fails and you want to show a custom error page instead, it's good
practice to use an _emitter_ instead of just dumping to `STDOUT`. The emitter
takes care of setting the correct headers:

```php
<?php

use Zend\Diactoros\Response\SapiEmitter;
use Zend\Diactoros\Response\HtmlResponse;

// ...
try {
    $controller->run();
} catch (Throwable $e) {
    $response = new HtmlResponse('The server did a boo boo', 500);
    $emitter = new SapiEmitter;
    $emitter->emit($response);
}
```

