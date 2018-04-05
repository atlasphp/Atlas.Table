# Atlas.Table

A table data gateway implementation for Atlas. Though it is primarily intended
to support [Atlas.Mapper][], it may be used independently of that package.

## Getting Started

First, you will need to create the prerequsite data-source classes using
[Atlas.Cli 2.x][].

Once you have done so, create a _TableLocator_ using the static `new()` method
and pass your PDO connection parameters:

```php
use Atlas\Table\TableLocator;

$tableLocator = TableLocator::new('sqlite::memory:'')
```

You can then use the locator to retrieve a _Table_ by its class name.

```php
use Atlas\Testing\DataSource\Thread\ThreadTable;

$threadTable = $tableLocator->get(ThreadTable::CLASS)
```

From there you can fetch, insert, update, and delete _Row_ objects.

In the absence of full documentation, please review these _Table_ methods
instead:

- fetchRow()
- fetchRows()
- select()
- newRow()
- insertRow()
- updateRow()
- deleteRow()

[Atlas.Cli 2.x]: https://github.com/atlasphp/Atlas.Cli
[Atlas.Mapper]: https://github.com/atlasphp/Atlas.Mapper
