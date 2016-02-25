# Dependencies
Dependency injection (also know as Inversion of Control, or IoC) is a design
pattern where consuming classes do not directly instantiate other classes (their
"dependencies") but rather receive them from a centralised "container". The idea
is that as long as injected objects are compatible with an expected interface,
you can easily swap them out for mocks (e.g. during unit testing).

Monolyth recommends doing dependency injection using
[Disclosure](http://disclosure.monomelodies.nl). But there are many other
libraries that can accomplish the same task, e.g.
[Pimple](https://packagist.org/packages/pimple/pimple). It really depends on
your preference (and, in fact, you can choose not to use dependency injection
_at all_, though we'd advise against that particular route :)).

Since `Http\Controller` is IoC-agnostic, you can either load your dependencies
in their own `pipe`, `require` an external file or if your project is _really_
simple, define them in `index.php` itself:

```php
<?php

// Poor man's dependency injection:
$foo = new Foo;

// ...in some other class...
class Bar
{
    private $foo;

    public function __construct()
    {
        $this->foo = $GLOBALS['foo'];
    }
}
```

More information: [Disclosure documentation](http://disclosure.monomelodies.nl)

