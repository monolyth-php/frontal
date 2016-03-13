# Testing your code
Writing tests for your application is _very_ important if it's anything remotely
serious, but often gets skipped due to lack of time or money, or maybe you've
simply inherited a project without any tests. Are you going to write tests for a
gazillion classes after the project is otherwise done? Probably not, and almost
certainly the client won't be paying for it ;)

PHPUnit is a popular tool for unit testing, but it comes with limitations. There
are various other frameworks for testing out there, but we personally prefer
[Gentry](http://gentry.monomelodies.nl). Gentry can perform unit tests,
integration tests and acceptance tests, is _much_ faster with fixtures than
PHPUnit with DBUnit is, analyses your suite of tests and informs you about
problems like untested code _and_ can also generate test skeletons for you to
get you up and running.

It works by looking at a `Gentry.json` config file in the root of a project
(we've supplied a basic example), analysing both the source directory as well as
existing tests, and updates as needed. Generated tests are of course empty since
it can't guess _what_ you need to test, but at least it saves you writing all
the boilerplate code. Additionally, it marks the tests as "incomplete" so it'll
complain when you run them, and you can quickly `grep` your tests folder to find
out which ones are sill a todo (and might we suggest starting with the most
important ones ;)).

To use it, setup your `Gentry.json` correctly and simply run it:

`vendor/bin/gentry`

We highly recommend integrating unit tests into your workflow, e.g. running them
as part of a Git `pre-push` hook that denies the push if anything's wrong. It's
saved our behind quite a few times, we all make mistakes after all.

More information: [Gentry documentation](http://gentry.monomelodies.nl)

