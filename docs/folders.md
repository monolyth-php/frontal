# Folder layout
Monolyth recommends a certain folder layout. You're free to change this to your
own liking, but we've found the following a sane default for modern projects:

- `httpdocs` contains your public files. This means all static assets as wel as
  the entry point (`index.php`). Apart from aforementioned index, it should
  not contain any executable PHP code and all Javascript/CSS code should ideally
  be minified/uglified. There are some exceptions to this (see later on), but
  these should be denied direct access in your server configuration.
- `src` contains your source code. For PHP files, it should be in the autoloader
  configuration for Composer (e.g. `"psr-4": {"": "src/"}`).
    - `src/_sass` contains SASS partials. For huge projects you might want to
      move each partial into a module directory, but usually keeping them
      together makes it easier to write build tasks using e.g. Grunt
      (`./src/_sass/**/*.scss` etc.)
- `docs` should contain your documentation, if you have any. We personally
  prefer `mkdocs` in combination with `phpdocumentor` and/or `esdoc` (for API
  documentation), but feel free to use whatever you want.
- `tests` contains your unit tests. For hybrid projects (combining e.g. PHP and
  AngularJS code) we usually create subdirectories, e.g. `tests/phpunit` and
  `tests/karma`. Again, choose your own test framework (the default is PHPUnit).

Additionally, we have the following recommendations for extra folders. You can
ignore them safely if your project doesn't need or use them, or if you prefer
something different:

- `info` for informational files. This is essentially any text file that doesn't
  comprise documentation, e.g. release notes or a todo list.
    - `info/sql` for your SQL schema(s).
- `bin` for executables.

> Note that if you change any of these (e.g. `src` to `source`) you'll probably
> need to modify a few `include` statements here and there as well, especially
> in `index.php` which sets things up.

