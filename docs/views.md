# Views
Monolyth has a slightly different take on Views than most MVC frameworks out
there (at least in the PHP world). In MVC theory, a "view" is an object that is
responsible for its own data. Most frameworks treat the HTML template as the
"view" and have controllers get and inject data into it. This is simply wrong.

To fix that we use `Improse`, a simple object oriented view wrapper. Routes
which only display data don't need a controller and should simply return a view
containing the requested data. We've supplied a simple example in
`./src/Welcome/View.php`.

The example view only specifies a protected `$template` member, which is in the
default setup the name of the Twig template to load. (You can use something else
too if that's more of your fancy.)

Views gathering data (e.g. through database queries based on a matched route
parameter) can define their own `__construct` and `__invoke` methods. Be sure to
also call the parent method though (except if you explicitly need to override of
course).

## Templates
The default templating engine is Twig. In our
[dependencies file](dependencies.md) you might have noticed the Twig setup (this
could also be done in the `View` base class, but since generally most calls use
Twig anyway we normally just keep it with the other dependencies, also because
we have the router there without having to perform mumbo jumbo).

The general naming scheme is `Some\Module\View.php` matches
`Some\Module\template.html.twig`, but that's really only a convention. Twig nor
Improse cares where you store what exactly, so if that's your "thang" you could
also store all templates in a separate `templates` folder.

## Twig extensions
A quick lookbehind to the dependencies file: notice how we do quite some things
regarding the Twig setup. Obviously we register the environment, but we also add
two Monolyth (well, module)-specific extensions:

1. We add a `url` Twig function. This is basically a front to `Router::generate`
   and allows us to reference named states in templates, e.g. `<a
   href="{{ url('home') }}">`. This is useful to keep URLs and states separated.
   Why? Well, imagine your URL scheme contains the current language. You don't
   want to have to write that in _every_ URL your site uses; instead, you would
   extend the Twig funtion to automatically set it if missing.
2. We load the `Metaculous` module. This offers a set of functions/filters to
   easily and consistently add meta tags to all your pages.

> The example doesn't actually implement Metaculous; but see their documentation
> for examples on how to do that.

## Parent templates
To inject our page into a parent template we use Twig's `{% extends %}` tag, and
place the specific page in a `{% block content %}`. The block name again is a
convention, you can use anything you like.

More information:

- [Improse documentation](http://improse.monomelodies.nl)
- [Metaculous documentation](http://metaculous.monomelodies.nl)
- [Twig documentation](http://twig.sensiolabs.org/documentation)

