# Dependencies
Monolyth uses the [Disclosure dependency injector](http://disclosure.monomelodies.nl).
We like to define dependencies in a central place so we can easily supply a file
with mock dependencies during testing. But, YMMV.

The classes in this demo won't have many dependencies, but we _did_ say we were
going to use a database. A prime candidate for dependency injection! Let's add
that to the `src/dependencies.php` file:

```php
<?php

use Disclosure\Container;
use Dabble\Adapter\Mysql;

Container::inject('*', function (&$adapter) {
    $adapter = new Mysql(
        'host=host;dbname=database',
        'user',
        'password',
        [
            PDO::ATTR_PERSISTENT => true,
            PDO::MYSQL_ATTR_INIT_COMMAND => "SET names 'UTF8'",
        ]
    );

});

```

The exact way Disclosure works is explained [in its
documentation](http://disclosure.monomelodies.nl/docs/), but suffice to say that
the above allows the member `$adapter` to be injected into any (`'*'`) object.

