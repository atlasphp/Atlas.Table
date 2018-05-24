# Reading Rows

## Fetching A Single Row

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

> **Note:**
>
> If `fetchRow()` does not find a match, it will return `null`.

## Fetching An Array Of Rows

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

> **Note:**
>
> If `fetchRows()` does not find a match, it will return an empty array.

## Query Construction

The `Table::select()` method returns a query object that you access to all the
underlying SQL query methods. See the [query system](/cassini/query)
documentation for more information.
