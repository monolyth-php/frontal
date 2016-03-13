# Sending emails
Often projects will need to send out emails. PHP has a `mail` function for that,
but it's rather limited. Something like [Swift](http://swiftmailer.org/) is a
much better choice.

_We_ suggest using [Emily](http://emily.monomelodies.nl) which is based on Swift
but also integrates Twig templates. This means your (HTML) mails can use the
same logic your Twig templates do, including defining blocks, per-mail
overriding, using variables etc.

Explaining how this all works goes way beyong the scope of this documentation,
so have a look at their documentation if you want to supercharge your
application's (HTML) mails.

More information: [Emily documentation](http://emily.monomelodies.nl)

