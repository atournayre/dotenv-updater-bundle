.env updater bundle
=================

The .env updater bundle helps managing .env.*.php files.

---

Getting Started
---------------
```
$ composer require atournayre/dotenv-updater-bundle
```

Configuring
----------------------
Enable the bundle
```php
# config/bundles.php
return [
    // ...
        Atournayre\DotEnvUpdaterBundle\AtournayreDotEnvUpdaterBundle::class => ['all' => true],
    // ...
];
```

Usage
----------

Files .env.*.php are only updated, not created.

### Update .env.local.php from .env
```
$ php bin/console dotenv:update
```

#### Example : update .env.prod.php from .env
```
$ php bin/console dotenv:update .env.prod.php
```

### Debug
Get list of variables and values defined in the .env.*.php file.
```
$ php bin/console dotenv:update --debug
```

### Update specific variable in .env.local.php
```
$ php bin/console dotenv:update:update
```

### Update specific variable in .env.*.php
```
$ php bin/console dotenv:update:update <.env.*.php>
```
