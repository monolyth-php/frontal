# Routing
Since routing is what makes an MVC project tick, let's have a quick look at
that. Open `./src/routing.php`.

The Reroute `Router` is very flexible and powerful, but its basic use should be
apparent from the example routing file: you define a `when("url regex")` to
match a request, and specify a `then('name of state', 'handlerfunction')` to
respond to that.

Routes can be nested like so:

```php
<?php

$router->when('/foo/', function ($router) {
    // This will match /foo/bar/:
    $router->when('/bar/')->then('bar', function () { /* ... */ });
});
```

Routes or groups of routes can also take a lamba as optional second argument
returning `false` if for any reason the route should not run:

```
<?php

$router->when('/account/', function () {
    if (!isset($_SESSION['User'])) {
        header("HTTP/1.1 403 Access denied");
        return false;
    }
})->then('accont', function () { /* ... */ });
```

You can also combine a check and a grouping; in that case the "grouper" becomes
the third argument.

> Reroute internally decides if something is a check or a "grouper" depending on
> the arguments the callback accepts; only callbacks with a single argument
> called `$router` are assumed to be groupers.

Routes can also accept (named) parameters:

```
<?php

$router->when("/profile/(?'userid'\d+)/")->then('profile', function ($userid) {
});
```

Unnamed parameters are passed to the callback in the order they were matched;
named parameters are matched by variable name so they're less ambiguous.

You can also inject the special `$VERB` variable into the callbacks which
contains the request type (e.g. `GET` or `POST`).

Both `then` callbacks as well as checking functions can use the matched
variables as well as `$VERB`. There's no need to pass every match (though that
will usually make sense for the `then` callback at least).

Note that for nested routes, _all_ matched variables are available in the
callbacks. So this will do what you expect:

```
<?php

$router->when("/(?'language'[a-z]{2})/", function ($router) {
    $router->when("/(?'page'\d+)/")->then(function ($language, $page) {
    });
});
```

Finally, a special route with `null` as the URL can be defined to match
something random and obviously never valid. This is useful for defining states
that shouldn't be accessible directly, but do error handling (404, 500 etc.).

More information: [Reroute documentation](http://reroute.monomelodies.nl)

