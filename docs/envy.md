# Environments
Unless your project is superduper simple, you'll run into the concept of
_environments_. An environment is simply the context in which your application
is currently run, e.g. `dev` or `prod`, or `web` or `cli`.

The poor man's solution to this is to define globals or constants with special
names, or even use `php.ini` settings or operating system environment variables.
Monolyth suggests a better solution: [Envy](http://envy.monomelodies.nl).

For example, on your development machine you might have a database `examplecom`
with user `monomelodies` and password `supersecret`. When [running
tests](gentry.md) however, you might want to use the database `exampletest`
with user `tester` and password `testing`. In production, finally, the hosting
company supplied you with something entirely different like database
`db678657654432_example`, user `exampleuser` and password `67juhtddffg57`. They
might even require you use a different hostname, which you don't want to have to
emulate on your dev box.

## Setting up
Envy works with a configuration file. We already supplied an empty one in the
root of the project (`Envy.json`) for you ;).

> Envy supports multiple formats for the config file including Yaml. See their
> documentation for all available format options.

Instantiate an Envy object as your environment and pass in the path to the
config file as a first argument, and an optional callback as a second argument.
Since we don't know where we're going to need it, we might as well register it
with [Disclosure](dependencies.md) while we're at it:

```php
<?php

use Envy\Envy;

// ...

$container->register(function (&$env) {
    $env = new Envy('/path/to/config.json', function ($env) {
        return ['array', 'of', 'applicable', 'environments'];
    });
});
```

## Creating a configuration
We'll assume you stick with the JSON format for now, but the instructions are
comparable for other formats.

Each root level key in the JSON is an environment name. Each key under that is
a variable you want to have for that environment. The return value of the second
argument to the Envy constructor should be the current environment (so you'll
usually want to include that function, otherwise using Envy wouldn't be very
useful ;)).

The return value of the callable can also be an array if multiple environments
apply (e.g. both `dev` and `cli`). If a key isn't found in the first environment
defined, Envy tries the second etc. Using this you can effectively "extend"
environments, e.g. use everything from `"dev"` but with a custom database
password if the user is `"monomelodies"`. The returned array would then look
something like `['monomelodies', 'dev']`. Note the order - the more specific
environment (our username) should come first.

The callable is also passed the new Envy instance, which you can use to
hard-code certain keys. This is useful for setting stuff like the name of the
current server or user:

```php
<?php

// ...
$env = new Envy('/path/to/config', function ($env) {
    $env->username = get_current_user();
    return ['one', 'or', 'more', 'environments'];
});
```

Inside the callable, make whatever check applies to your situation and return
the current environment(s).

## Using placeholders
Inside the config, you can also use placeholders. Note that these are parsed
"non-recursively", i.e. only one level deep:

```json
{
    "dev": {
        "mail_from": "<% username %>@example.com"
    }
}
```

If `$env->username` exists, Envy will replace it with the defined value. See the
example above on how you would set these "globals".

## Checking the current environment
Besides the values relevant to the current environment, you can also check if a
certain environment was loaded:

```php
<?php

var_dump($env->dev); // true if the `dev` environment applies, else false.
```

> Preventing naming conflicts between environments and variables is up to you.

More information: [Envy documentation](http://envy.monomelodies.nl)

