# Quickly adding an API
The `Monki` module allows you to quickly bootstrap an API (presuming it needs
to interface with a PDO database). In its core it assumes API routes are of the
form `/tablename/[(key|count)/]` and optional `action` values get posted.

See the documentation for more extensive examples, but to use it do something
like this (assuming `$router` is a Reroute Router, this is a dependency Monki
has):

```php
<?php

// Usually, you'll want to prefix API routes or even have them come from a
// separate domain (e.g. https://api.example.com).
$router->when('/api/', function ($router) use ($adapter) {
    // $adapter should contain a PDO object
    $monki = new Monki\Api($adapter, $router);
    // The API is browsable:
    $monki->browse();
    // The API supports `count` on object types:
    $monki->count();
    // The API support operations on a single object:
    $monki->item();
});
```

More information: [Monki documentation](http://monki.monomelodies.nl)

