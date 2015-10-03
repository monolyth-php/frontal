# Monolyth
Extremely loosely coupled PHP5 MVC unframework, done right

Monolyth is designed as an extremely loosely coupled PHP5 MVC unframework,
correcting a number of problems (we find) with existing frameworks out there.
Where possible, we have let ourselves be inspired by existing frameworks, both
inside and outside of the PHP world. Where necessary, we chose our own path :)

- [Website](http://monolyth.monomelodies.nl)
- [Documentation](http://monolyth.monomelodies.nl/docs/)

## Installation

### Composer project (recommended)
`$ composer create-project monomelodies/monolyth /path/to/new/project`

Ehm, that's it, really. Well, your web server needs to serve from
`/path/to/new/project`, you might want to add it to version control and you'll
probably need a database, but you get the idea.

### Composer dependency
To migrate an existing project to Monolyth, install as a composer _dependency_
instead:

`$ cd /path/to/existing/project && composer require monomelodies/monolyth`

You'll probably want to copy the `httpdocs` and `src` directories into your
project, since they're meant as example templates and will have to be edited by
the implementor (i.e., you).

Using this strategy, _all_ Monolyth dependencies are loaded and you cannot
prune. Also, any updates to Monolyth will be loaded whenever you do
`composer update`, along with updates to Monolyth dependencies. That's not
really how it's designed to work - the dependencies are rather recommendations
(but `composer create-project` doesn't honour the `suggest` key) and your
project might require different versions than the latest Monolyth recommends.
So in the real world (if you don't mind having the extra depedencies floating
around in `./vendor`) when using this strategy you should lockdown Monolyth to
the exact version you installed with, or your project might break in unexpected
ways in the future!

### Download, cherry pick, remove
This is actually closer to the recommended installation, as it's really a manual
version of what `composer create-project` does:

1. Download or clone the library somewhere (doesn't matter where):
   `git clone https://github.com/monomelodies/monolyth.git some/path`
   or
   `wget https://github.com/monomelodies/monolyth/archive/master.zip`
   `mkdir some/path && mv master.zip some/path/`
   `cd some/path && unzip master.zip`
2. Copy what you need into `/path/to/project`. From Monolyth's `composer.json`,
   be sure to review the dependencies and remove what you're not going to use.
3. When done, you can `rm -rf` the cloned or downloaded repository.

## What Monolyth is _NOT_
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
- Replace this README.md with something applicable for your project;
- Optionally add extra Composer dependencies;
- Optionally setup build scripts (e.g. using Grunt);
- Prune your `composer.json` file - we've probably installed dependencies you
  don't really need anyway;
- Make sure the `./httpdocs` folder (or whatever you decide to call it) points
  to the public root for your testdomain (e.g. `http://localhost/`);
- Whip up your favourite browser and navigate to `http://localhost/`. There's a
  welcome page to get you started.
- Go build that awesome project!

