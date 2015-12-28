# Sending emails
Often projects will need to send out emails. PHP has a `mail` function for that,
but it's rather limited. Something like `Swift` is a better choice.

We suggest using `Emily` which is a Switf-based mailer integrated with Twig
templates. This means your HTML mails can use the same logic your Twig templates
do, including defining blocks, per-mail overriding etc.

Explaining how this all works goes way beyong the scope of this documentation,
so have a look at their documentation if you want to supercharge your HTML
mails.

More information: [Emily documentation](http://emily.monomelodies.nl)

