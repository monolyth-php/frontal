# Monolyth
Extremely loosely coupled PHP5 MVC unframework, done right

Monolyth is designed as an extremely loosely coupled PHP5 MVC unframework,
correcting a number of problems (we find) with existing frameworks out there.
Where possible, we have let ourselves be inspired by existing frameworks, both
inside and outside of the PHP world. Where necessary, we chose our own path :)

- [Website](http://monolyth.monomelodies.nl)
- [Documentation](http://monolyth.monomelodies.nl/docs/)

## Installation

### Composer (recommended)
```bash
$ cd /path/to/project
$ composer require monomelodies/monolyth
```

You'll note that Monolyth installs a few dependencies, but mostly does a lot of
_recommendations_. You're free to follow up on these or not. Note that most of
the manual assumes these packages are also `composer require`d, though.

### Manual
1. Download or clone the library somewhere to your liking;
2. Register `/path/to/monolyth/src` for the `Monolyth\\` namespace in your
   autoloader;
3. Repeat for desired submodules (see `composer.json` for a list of recommended
   additional modules).

## What Monolyth is _not_
Monolyth is _not_ a full stack framework, at least not as you'll know it.
Rather, it is a set of best practices surrounded by a number of (sub)modules
that work nicely together, but mostly can also be used on their own. You can mix
and match modules to _assemble_ a full stack framework, or use modules in
conjunction with your favourite other framework if you find our versions more
convenient.

Monolyth is _not_ a package manager; utilities like Bower or Composer are much
better suited to this task. If you're not worried about version mismatches, you
could also just add modules as Git submodules and be done with it. We don't want
to tell you how you should work!

Monolyth is _not_ here to tell you what to do. We can help you if you let us,
but our design goal is to make optional what we can.

## So what _is_ Monolyth then?
The name is a pun; Monolyth is anything but monolithic. We have tried to
abstract every function you might need for an MVC PHP project into an isolated
module, and allow you to mix and match what you find convenient. Essentially,
you've just installed a bunch of Composer dependencies we recommend, as well as
a fairly generic project skeleton (which you should adapt to reflect your own
needs of course).

Our [Wiki](http://monolyth.monomelodies.nl/wiki/) contains a full example
project with code and explanation, and will also tell you more about the various
modules and how they work together.

> *Note:* the current major version is 6, but is a complete rewrite from the
> older versions.

## Now what?
- Optionally add extra Composer dependencies;
- Optionally setup build scripts (e.g. using Grunt);
- Make sure the `./httpdocs` folder (or whatever you decide to call it) points
  to the public root for your testdomain (e.g. `http://localhost/`);
- The `./example/httpdocs` folder contains an example entry point;
- The `./example/src` folder contains our version of "Hello world". Copy it into
  your own `./src` or whatever folder to get started;
- Go build that awesome project!

