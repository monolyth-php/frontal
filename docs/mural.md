# Multiple sites in one project
If you have multiple related projects (our favourite example is a group of
dating sites with specific variants for straights, gays and lesbians) you'll
want to be able to share a lot of code, and only override what's actually
different.

The `Mural` module is a multiple resource autoloader that allows you to define
the overrides in their own namespace (so everything remains testable), whilst
allowing your code to refer to the "generic" classname.

An example:

```php
<?php

// This would go in a file specific to one of the sites in the projhect, so
// typically `index.php` is a fine candidate:
$mural = new Mural\Autoloader;
$mural->rewrite('\\', 'Straight\\'); // Or 'Gay\\' or 'Lesbian\\'
```

Now let's say our dating platform needs to perform a search for the currently
logged in user. Obviously, the straight variant should yield results of the
_opposite_ sex, whereas the other two should yield results of the _same_ sex.

Assuming the search itself is performed by a `Search\View`, a shared routing
table could simply load that if the path matches `/search/`. Mural makes sure
that, if it exists, e.g. `Straight\Search\View` is preferred instead. No need
to duplicate the route just because of that, do complicated and error-prone
`if/else` statements in the view or abuse [`Envy`](envy.md) to check the correct
environment - just refer to the "global" name and Mural figures it out.

If the searching is the only bit that differs, you can also be more specific:

```php
<?php

$mural->rewrite('Search\\', 'Straight\\Search\\');
```

This is obviously slightly more efficient.

In your unit tests, you can now explicitly test `Straight\Search\View` as well
as `Gay\Search\View` (etc.) to see if they returns results of the correct sex,
without any naming conflicts or the need to run multiple test suites.

More information: [Mural documentation](http://mural.monomelodies.nl)

