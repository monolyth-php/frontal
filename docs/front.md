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

Monolyth comes bundled with `filp/whoops`, which is a nice little library that
quickly formats your error pages. The `Http\Controller` offers a public `whoops`
method to set this up:

```php
<?php

use Whoops\Handler\PrettyPageHandler;

// ...

$controller->whoops(new PrettyPageHandler);
```

Whoops comes bundled with some other handlers, e.g. `JsonResponseHandler`. It's
up to you to pick the right one for your application.

