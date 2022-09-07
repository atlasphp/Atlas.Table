# Getting Started

## Installation

This package is installable and autoloadable via [Composer](https://getcomposer.org/)
as [atlas/table](https://packagist.org/packages/atlas/table). Add the following lines
to your `composer.json` file, then call `composer update`.

```json
{
    "require": {
        "atlas/table": "~1.0"
    }
}
```

## Upgrading

To upgrade from the prior version of Atlas.Table, you only need to re-run the
[`atlas-skeleton-upgrade`](/dymaxion/skeleton) command with your existing
configuration.

## Instantiation

Before using Atlas.Table for the first time, you will need to create the
prerequsite data-source classes using the [`atlas-skeleton`](/dymaxion/skeleton)
command.

Once you have your data source classes in place, create a _TableLocator_ using
the static `new()` method and pass in an Atlas.Pdo _Connection_:

```php
use Atlas\Pdo\Connection;
use Atlas\Table\TableLocator;

$connection = Connection::new('sqlite::memory');
$tableLocator = TableLocator::new($connection);
```

Alternatively, you can pass an existing PDO instance, or a set of PDO
constructor arguments:

```php
// existing PDO connection
$pdo = new PDO('sqlite::memory');
$tableLocator = TableLocator::new($pdo);

// PDO constructor arguments
$tableLocator = TableLocator::new('sqlite::memory');
```

You can then use the _TableLocator_ to retrieve a _Table_ by its class name.

```php
use Atlas\Testing\DataSource\Thread\ThreadTable;

$threadTable = $tableLocator->get(ThreadTable::CLASS);
```

From there you can select, insert, update, and delete _Row_ objects on a table,
or work with table-level query objects.
