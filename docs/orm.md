# Object-relational mapper
Our own preferred take on ORM is to use
[Ornament](http://ornament.monomelodies.nl) along with
[Dormant](http://dormant.monomelodies.nl) (a [Dabble](dabble.md) adapter for
Ornament).

We're not going to go into Ornament too much here; it has its own extensive
documentation. But as a quick example, look at the (otherwise unused) model in
`./src/Welcome/Model.php`.

This example/skeleton model already contains the essence of an Ornament model
(so feel free to copy/paste ;)). We register an adapter to use (in this case,
the `Dormant\Dabble` adapter, but others could be used e.g. `Ornament\Pdo` for
a stock PDO database instance, or something of your own making). Assuming your
database contains a table `welcome` with fields `id`, `name` and `value` you
could now create, modify, store and delete models:

```php
<?php

$welcome = new Welcome\Model;
$welcome->name = 'Marijn';
$welcome->value = 'priceless';
$welcome->save();
// Assuming `id` is an auto-increment column:
echo $welcome->id; // 1

// Querying:
$me = Welcome\Model::find(['id' => 1]);
echo $me->name; // "Marijn"
$me->delete();

$me = Welcome\Model::find(['id' => 1]);
var_dump($me); // null
```

> Note that Ornament is not some kind of active record-like ORM per se. It
> doesn't require extensive configuration files, and can transparently interact
> with any storage engine (including APIs!) it has an adapter for.

More information:

- [Ornament documentation](http://ornament.monomelodies.nl)
- [Dormant documentation](http://dormant.monomelodies.nl)

