# Instantiation

Before using Atlas.Table, you will need to create the prerequsite data-source
classes using [Atlas.Cli 2.x](https://github.com/atlasphp/Atlas.Cli).

Once you have your data source classes in place, create a _TableLocator_ using
the static `new()` method and pass your PDO connection parameters:

```php
use Atlas\Table\TableLocator;

$tableLocator = TableLocator::new('sqlite::memory:'')
```

> **Tip:**
>
> Alternatively, you can pass an already-created Atlas.Pdo _Connection_ object.

You can then use the locator to retrieve a _Table_ by its class name.

```php
use Atlas\Testing\DataSource\Thread\ThreadTable;

$threadTable = $tableLocator->get(ThreadTable::CLASS)
```

From there you can select, insert, update, and delete _Row_ objects on a table,
or work with table-level query objects.
