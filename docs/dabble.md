# Database abstraction
Dabble is a database abstraction layer and query helper. It's quite packed with
features, but essentially it allows you to query in the following manner:

```php
<?php

$rows = $dabble->fetchAll(
    'tablename',
    $array_of_fields,
    $hash_of_where,
    $hash_of_options
);
```

We personally find this much more convenient than writing all SQL by hand
(though that's a fine craft and indeed sometimes necessary for complex queries).
For instance, the arrays can be programmatically built depending on conditions,
which is handy for `$where` arrays or fields to update/insert.

Dabble is an extension to `PDO`, so any code type-hinting PDO objects should
also work with a Dabble adapter. We personally recommend injecting the database
object where needed using [dependency injection](dependencies.md), and keeping
the credentials in your [environment setup](envy.md).

More information: [Dabble documentation](http://dabble.monomelodies.nl)

