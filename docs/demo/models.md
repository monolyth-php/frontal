# Models
As far as Monolyth is concerned, your models are simply "the layer that
manipulates your data". Exactly _how_ you do that is up to you.

You could for instance hook up an ORM like
[Doctrine](http://www.doctrine-project.org/projects/orm.html) to your project.
We're personally not huge fans of these types of ORM systems, since they
assume a logical coupling between the way your data is stored and what it
represents. E.g., each table in an SQL database corresponds to a class of
entity. For complex projects, we find it's not uncommon to have data for a
single logical business entity to be spread out over multiple tables or even
various storage systems (e.g., a User in an SQL database with a ChatLog stored
in CouchDB).

Anyway, what you use and how you come by it is up to you. Let's for our demo
though assume we have an object of class "UserModel" respresenting a user of
our system. The important thing here is: models are used in controllers. It's
the controllers job to call the correct methods on a model depending on the
action the user requested.

If you're using a system where models also perform select queries (e.g.
[Eloquent](http://laravel.com/docs/5.0/eloquent) you'll also use models in your
views. (The proper way is to separate `SELECT` and `CRUD` queries in different
objects, e.g. Doctrine's "Manager" classes).

```php
<?php

class Controller
{
    protected $user;

    public function __construct()
    {
        $this->inject(function (UserModel $user) {});
    }

    public function updatePassword()
    {
        $this->user->updatePassword($_POST['password']);
    }

    public function updateEmail()
    {
        $this->user->updateEmail($_POST['email']);
    }
    }
}


```

