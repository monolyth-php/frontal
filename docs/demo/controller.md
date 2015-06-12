# Controllers
In Monolyth, Controllers aren't really anything. We believe in Fat Models and
Skinny Controllers.

> By this we don't mean you should create "God objects" as models. It's best
> to keep all classes mean and lean ("do one thing and do it well"). Rather, it
> means that all *logic* should be in a model *somewhere*, not in your
> controllers.

In fact, you could write an entire application with even adding a "controller".
Using [Reroute](http://reroute.monomelodies.nl), each URL maps to a "state".
Since a state is essentially a PHP callable, you can see that lambda as the
"controller":

```php
<?php

$router->state('foo', new Flat('/foo/'), function () {
    // Wire stuff together controller-style!
    return 'some output';
});

```

For `GET` requests, you'll typically not use a controller - a `GET` is meant to
return just content, which should be loaded by the
[view](http://improse.monomelodies.nl). Views are responsible for their own
data.

Of course, for more complex projects you don't want all your logic in a routing
table. In those cases, feel free to use a controller of sorts. Just remember
that in the end, it's not necessarily the controller's job to even decide on
which view to use.

The above in mind, the following is our preferred pattern:

```php
<?php

$router->state('foo', new Flat('/foo/', ['GET', 'POST']), function ($VERB) {
    if ($VERB == 'POST') {
        $controller = new Controller;
        $controller->somePublicMethod();
    }
    return new View;
});

```

...and in the controller class:

```php
<?php

class Controller
{
    public function somePublicMethod()
    {
        // Do stuff with models?
    }
}

```

> An added benefit of using an actual class to glue stuff together is that you
> can utilise [dependency injection](http://disclosure.monomelodies.nl).

