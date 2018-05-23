# Usage

Before using Atlas.Table, you will need to create the prerequsite data-source
classes using [Atlas.Cli 2.x](https://github.com/atlasphp/Atlas.Cli).

## Instantiation

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

## Reading and Writing

From there you can select, insert, update, and delete _Row_ objects on a table.

In the absence of full documentation, please review these _Table_ methods
instead:

### Fetching A Single Row

Use the `fetchRow()` method to retrieve a single Row. It can be called
either by primary key, or with a `select()` query.

```php
// fetch by primary key thread_id = 1

$threadRow = $threadTable->fetchRow('1');

$threadRow = $threadTable
    ->select()
    ->where('thread_id = ', '1')
    ->fetchRow();
```

> **Tip:**
>
> The `select()` method gives you access to all the underlying SQL query
> methods. See [Atlas\Query](https://github.com/atlasphp/Atlas.Query/)
> for more information.

> **Note:**
>
> If `fetchRow()` does not find a match, it will return `null`.

### Fetching An Array Of Rows

The `fetchRows()` method works the same as `fetchRow()`, but returns an
array of Rows.  It can be called either with primary keys, or with a
`select()` query.

```php
// fetch thread_id 1, 2, and 3
$threadRows = $threadTable->fetchRows([1, 2, 3]);

// This is identical to the example above, but uses the `select()` variation.
$threadRows = $threadTable
    ->select()
    ->where('thread_id IN ', [1, 2, 3])
    ->fetchRows();
```

> **Tip:**
>
> The `select()` method gives you access to all the underlying SQL query
> methods. See [Atlas\Query](https://github.com/atlasphp/Atlas.Query/)
> for more information.

### Creating and Inserting A Row

Create a new Row using the `newRow()` method. You can assign data using
properties, or pass an array of initial data to populate into the Row.

```php
$threadRow = $atlas->newRow([
    'title' => 'New Thread Title',
]);
```

You can assign a value via a property, which maps to a column name.

```php
$date = new \DateTime();
$threadRow->date_added = $date->format('Y-m-d H:i:s');
```

You can insert a single Row into the database by using the `insertRow()` method:

```php
$threadTable->insertRow($threadRow);
```

> **Warning:**
>
> The insertRow() method will not catch exceptions; you may wish to wrap the
> method call in a try/catch block.

Inserting a Row with an auto-incrementing primary key will automatically
modify the Row to set the last-inserted ID.

### Updating an Existing Row

Updating an existing row works the same as `insertRow()`.

```php
// fetch an existing row by primary key
$threadRow = $threadTable->fetchRow(3);

// modify the title
$threadRow->title = 'This title is better than the last one';

// save the row back to the database
$threadTable->updateRow($threadRow);
```

> **Warning:**
>
> The updateRow() method will not catch exceptions; you may wish to wrap the
> method call in a try/catch block.

> **Note:**
>
> The updateRow() method will only send the row data **changes** back to the
> database, not the entire row. If there were no changes to the row data,
> calling updateRow() will be a no-op.

### Deleting a Row

Deleting a row works the same as inserting or updating.

```php
$threadRow = $threadTable->fetchRow(3);
$threadTable->deleteRow($threadRow);
```

> **Warning:**
>
> The deleteRow() method will not catch exceptions; you may wish to wrap the
> method call in a try/catch block.


## Table-Wide Operations

Whereas `insertRow()`, `updateRow()`, and `deleteRow()` operate on individual
Row objects, you can perform table-wide operations using `insert()`, `update()`,
and `delete()`. These latter three methods return Atlas.Query objects for you
to work with as you see fit; call `perform()` on them directly to execute the
query and get back a _PDOStatement_.

See the [Atlas.Query](/cassini/query/) documentation for more information on
the insert, update, and delete query objects.


## Table Events

There are several events that run at different points in the query object
creation and execution lifecycle:

```php
// runs after any Select query is created
function modifySelect(Table $table, TableSelect $select)

// runs after a newly-selected Row is populated
function modifySelectedRow(Table $table, Row $row)

// runs after any Insert query is created
function modifyInsert(Table $table, Insert $insert)

// runs after any Update query is created
function modifyUpdate(Table $table, Update $update)

// runs after any Delete query is created
function modifyDelete(Table $table, Delete $delete)

// runs before a Row-specific Insert query is created
function beforeInsertRow(Table $table, Row $row)

// runs after the Row-specific Insert query is created, but before it is performed
function modifyInsertRow(Table $table, Row $row, Insert $insert)

// runs after the Row-specific Insert query is performed
function afterInsertRow(
    Table $table,
    Row $row,
    Insert $insert,
    PDOStatement $pdoStatement
)

// runs before the Row-specific Update query is created
function beforeUpdateRow(Table $table, Row $row)

// runs after the Row-specific Update query is created, but before it is performed
function modifyUpdateRow(Table $table, Row $row, Update $update)

// runs after the Row-specific Update query is performed
function afterUpdateRow(
    Table $table,
    Row $row,
    Update $update,
    PDOStatement $pdoStatement
)

// runs before the Row-specific Delete query is created
function beforeDeleteRow(Table $table, Row $row)

// runs after the Row-specific Delete query is created, but before it is performed
function modifyDeleteRow(Table $table, Row $row, Delete $delete)

// runs after the Row-specific Delete query is performed
function afterDeleteRow(
    Table $table,
    Row $row,
    Delete $delete,
    PDOStatement $pdoStatement
)
```

For example, when you call `updateRow()`, these events run in this order:

- `beforeUpdateRow()`
- `modifyUpdate()`
- `modifyUpdateRow()`
- `afterUpdateRow()`

Note that merely calling `update()` to get a table-wide Update query will only
run the `modifyUpdate()` method, as it is not a row-specific interaction.

TableEvents are be the place to put behaviors such as setting `inserted_at` or
`updated_at` values, etc:

```php
namespace Blog\DataSource\Posts;

use Atlas\Table\Row;
use Atlas\Table\Table;
use Atlas\Table\TableEvents;

class PostsTableEvents extends TableEvents
{
    public function beforeInsertRow(Table $table, Row $row)
    {
        $row->inserted_at = date('Y-m-d H:i:s');
    }

    public function beforeUpdateRow(Table $table, Row $row)
    {
        $row->updated_at = date('Y-m-d H:i:s');
    }
}
```
