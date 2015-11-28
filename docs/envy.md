# Environments
Unless your project is superduper simple, you'll run into the concept of
_environments_. An environment is simply the context in which your application
is currently run, e.g. `dev` or `prod`, or `web` or `cli`.

The poor man's solution to this is to define globals or constants with special
names, or even use `php.ini` settings or operating system environment variables.
Monolyth suggests a better solution: `Envy`.

## Setting up
Envy works with a configuration file. We already supplied an empty one in the
root of the project (`Envy.json`) for you ;).

> Envy supports multiple formats for the config file including Yaml. See their
> documentation for all available format options.

Instantiate an Envy object as your environment and pass in the path to the
config file as a first argument, and an optional callback as a second argument.
Since we don't know where we're going to need it, we might as well register it
with Disclosure while we're at it:

```php
<?php

Container::register('*', function (&$env) {
    $env = new Envy('/path/to/config.json', function ($env) {
        return 'current_environment';
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
defined, Envy tries the second etc.

The callable is also passed the new Envy instance, which you can use to
hard-code certain keys. This is useful for setting stuff like the name of the
current server or user.

Inside the callable, make whatever check applies to your situation and return
the current environment(s).

## Using placeholders
Inside the config, you can also use placeholders. Note that these are parsed
"non-recursively", i.e. only one level deep:

```json
{
    "dev": {
        "mail_from": "<% user %>@example.com"
    }
}
```

If `$env->user` exists, Envy will replace it with the defined value.

## Checking the current environment
Besides the values relevant to the current environment, you can also check if a
certain environment was loaded:

```php
<?php

var_dump($env->dev); // true if the `dev` environment applies, else false.
```

> Preventing naming conflicts between environments and variables is up to you.

More information: [Envy documentation](http://envy.monomelodies.nl)

