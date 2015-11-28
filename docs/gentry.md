# Generating test skeletons
Writing unit tests is _very_ important for any serious project, but often gets
skipped due to lack of time or money, or maybe you've simply inherited a
project without any tests. Are you going to write tests for a gazillion classes
after the project is otherwise done? Probably not, and almost certainly the
client won't be paying for it ;)

`Gentry` is a simple tool that generates or extends test skeletons for as-yet
untested classes. It works by looking at a `Gentry.json` config file in the root
of a project (we've supplied a basic example), analysing both the source
directory as well as existing tests, and updates as needed. Generated tests are
of course empty since it can't guess _what_ you need to test, but at least it
saves you writing all the boilerplate code. Additionally, it marks the tests
as "incomplete" for PHPUnit so it'll complain when you run them, and you can
quickly `grep` your tests folder to find out which ones are sill a todo (and
might we suggest starting with the most important ones ;)).

To use it, setup your `Gentry.json` correctly and simply run it:

`vendor/bin/gentry`

Gentry won't touch any existing tests, so you can safely run it incrementally.

More information: [Gentry documentation](http://gentry.monomelodies.nl)

