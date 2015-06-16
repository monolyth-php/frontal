# Prerequisites
Some of this is going to be glaringly obvious, but there's a few things we'll
need before we can build our demo site.

## A working webserver
Obvious, but since some full stack frameworks offer a built-in testing server we
thought we'd best mention it. Your server will need some form of URL rewriting
enabled (e.g. `mod_rewrite` in the Apache server) and a database (the examples
all assume MySQL, which is the most common web database anyway).

> Monolyth doesn't _require_ a database to be used, but our demo site will. And
> besides, virtually all but the most simple projects will need one.

You'll also of course need a current version of PHP - 5.5 minimum.

## Recommended setup
We're opinionated on the recommended setup. Of course, Monolyth will work just
as well if you simply bung all your files in your public root, but really you
shouldn't do that. Only public files should be in a public facing place.

Our recommended directory structure is as follows (the actual directory names
being a matter of taste of course):

    /path/to/account
        /httpdocs <- the public root
            /css
            /js
            index.php
            ...etc...
        /src <- PHP files for your project
        /tests <- Unit tests
        /bin <- executables
        /bower_components <- Bower packages
        /node_modules <- Node modules
        /vendor <- Composer packages
            /monolyth
            ...etc...
        composer.json
        composer.lock
        phpunit.xml

In your `composer.json`, register the `src` directory with the autoloader:

```json
{

    "autoload": {
        "psr-4": {
            "": ["src/"]
        }
    }
}
```

Optionally, you could set your `include_path` to include `/path/to/account/src`
for easy access.

If you have different preferences on how to organise your code, be our guest. As
long as the autoloader points to the right directory containing your PHP files,
it's all fair game.

## `index.php`, URL rewrites and other "required" files
`index.php` will act as a front controller, so we'll need to rewrite any URL
that points to something non-existing to it. In Apache, this is done as follows:

    <IfModule mod_rewrite.c>
        RewriteEngine On
        RewriteBase /
        
        RewriteCond   %{DOCUMENT_ROOT}/$1                  !-f
        RewriteCond   %{DOCUMENT_ROOT}/$1                  !-d
        RewriteCond   %{DOCUMENT_ROOT}/$1                  !-l
        
        RewriteRule     ^(.*?)$ /index.php [L]
    </IfModule>

There's an example `index.php` in Monolyth's `httpdocs` folder you can copy and
adapt to suit your needs. You'll notice it `require`s two additional files:
`[src/]dependencies.php` and `[src/]routing.php`. These are explained in the
subsequent chapters.

