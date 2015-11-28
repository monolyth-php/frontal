# The front controller
So you're setup correctly and are looking at our _beautiful_ (ahum) welcome page
in your favourite web browser. Let's look at what's going on here:

## `index.php`
This is your "front controller". A front controller, in MVC terms, is sort of
your central hub where all non-static requests get funelled to, and that decides
what should happen next (usually based on some routing table). Open `index.php`
in a text editor.

You'll see that it `include`s the Composer autoloader, and does a few generic
operations (essentially fix PHP's handling of UTF-8, and a quick check for sites
behind a proxy - YMMV depending on your server software/proxy brand).

After that's done, it `include`s the _dependencies file_. Monolyth recommends
[Disclosure](http://disclosure.monomelodies.nl) for dependency injection, but
you can swap it for something else if you like (note that some modules do depend
on it themselves, but Composer should figure that out). In Monolyth, the
dependencies also load the routing table so you don't have to worry about that
in your front controller.

Next it starts PHP sessions using [Cesession](http://cesession.monomelodies.nl).
Except for the simplest projects it's highly recommended to not use the default
PHP session handlers, since they work on flat files and have locking issues.
Having said that, if you don't need to use sessions at all you can just delete
those lines - saves you the overhead of a cookie.

Finally, it attempts to resolve the current `REQUEST_URI` using `$router` (that
was defined in the dependencies). The default is to use
[Reroute](http://reroute.monomelodies.nl), which on succesfull resolve returns
a `State` object. Invoking this should yield your output (HTML in our case).

And if any exception gets thrown, your front controller should handle that
gracefully (or not so gracefully if you're developing :)).

> For more complex projects, your front controller will likely do more, e.g.
> check the `$state->name` for special handling.

