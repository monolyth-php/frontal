# Dependencies
By default Monolyth does dependency injection using
[Disclosure](http://disclosure.monomelodies.nl). Let's see how that works in the
example `src/dependencies.php` file.

## The `Container` class
This forms the central hub for dependency management in Disclosure. Usually it
will be called in the following manner:

```
<?php

Conainer::inject('*', function (&$foo) {
    $foo = new Foo;
});
```

This registers a `Foo` object on the variable name `$foo`. The naming similarity
is convenience; you can call it whatever suits you. The `'*'` identifier means
this dependency can be injected into _any_ object. You can also specify a
fully qualified (parent) class, interface or trait name to be more specific on
where this can be injected.

Each injection declaration can defined an arbitrary number of dependencies.
Consuming classes don't have to import them all; they should specify explicitly
which dependencies they need.

## The `Injector` trait
Consuming classes `use` the `Injector` trait. This offers a single method:
`inject`.

```
<?php

class Bar
{
    use Disclosure\Injector;

    public function __construct()
    {
        $this->inject(function ($foo) {});
        // $this->foo is now the Foo object from earlier!
    }
}
```

`inject` can also be called statically to define dependencies directly on a
class:

```
<?php

Bar::inject(function (&$foo) {
    $foo = new Foo;
});
```

This is equivalent to `Container::inject('Bar', ...)`.

The static style makes sense for dependencies that aren't used often and is
usually used alongside the actual class definition ("just-in-time").

More information: [Disclosure documentation](http://disclosure.monomelodies.nl)

