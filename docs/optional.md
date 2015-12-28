# Did you say "optional"?
By now you have a simple working site and should be able to add more routes,
views and templates. But you'll notice Monolyth loaded a bunch of other
dependencies. What's with those? Well, technically they're recommendations, but
since Composer doesn't let us "suggest" modules during a `create-project`
action, we've just added them for you. Feel free to remove what you don't intend
to use.

The following chapters are going to quickly explain what each module is for. You
can decide what you want to keep afterwards :)

To remove a module, just delete it from your `composer.json` and run `composer
update`.

> Note that the above won't work if you installed Monolyth via `composer
> require`. In that case, you'll just have to ignore the superfluous modules -
> they won't do you any harm, just take up some disk space.

