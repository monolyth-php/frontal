# Views and templates
Monolyth has a slightly different take on Views than most MVC frameworks out
there (at least in the PHP world). In MVC theory, a "view" is an object that is
responsible for its own data. Most frameworks treat the HTML template as the
"view" and have controllers get and inject data into it. This is simply wrong.

To fix that we created `Improse`, a simple object oriented view wrapper. Routes
which only display data don't need a controller and should simply return a view
containing the requested data. It is then the view's job to render this data
using some _template_, which could be HTML, PHP or something like
[Twig](http://twig.sensiolabs.org) (our preferred choice). The important thing
is, it should once again eventually end up as an object implementing
`Psr\Http\Message\ResponseInterface`.

More information:

- [Improse documentation](http://improse.monomelodies.nl)
- [Twig documentation](http://twig.sensiolabs.org/documentation)

> As always, you're free to ignore our advice on this and implement controllers
> with their own HTML generation logic. As long as you wrap that HTML in a
> `ResponseInterface`-compatible object, the `Http\Controller` is fine with
> that.

