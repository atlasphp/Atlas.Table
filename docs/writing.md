# Writing To The Table

## Creating and Inserting A Row

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

Inserting a Row into a table with an auto-incrementing primary key will
automatically modify the Row to set the last-inserted ID.

## Updating an Existing Row

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

## Deleting a Row

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

## Identifier Quoting

The `insert()`, `update()`, and `delete()` methods will automatically quote the
table name in the INTO and FROM clauses.

In addition, the _Insert_ and _Update_ query objects themselves will
automatically quote the column names being inserted or updated.

You can manually quote any other identifiers using the query object's
`quoteIdentifier()` method.
