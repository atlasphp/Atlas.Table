# Table Events

Each generated _Table_ class has its own corresponding _TableEvents_ class. The
_TableEvents_ methods are invoked automatically at different points in the
_Table_ interactions:

```php
// runs after any Select query is created
function modifySelect(Table $table, TableSelect $select) : void

// runs after a newly-selected Row is populated
function modifySelectedRow(Table $table, Row $row) : void

// runs after any Insert query is created
function modifyInsert(Table $table, Insert $insert) : void

// runs after any Update query is created
function modifyUpdate(Table $table, Update $update) : void

// runs after any Delete query is created
function modifyDelete(Table $table, Delete $delete) : void

// runs before a Row-specific Insert query is created
function beforeInsertRow(Table $table, Row $row) : ?array

// runs after the Row-specific Insert query is created, but before it is performed
function modifyInsertRow(Table $table, Row $row, Insert $insert) : void

// runs after the Row-specific Insert query is performed
function afterInsertRow(
    Table $table,
    Row $row,
    Insert $insert,
    PDOStatement $pdoStatement
) : void

// runs before the Row-specific Update query is created
function beforeUpdateRow(Table $table, Row $row) : ?array

// runs after the Row-specific Update query is created, but before it is performed
function modifyUpdateRow(Table $table, Row $row, Update $update) : void

// runs after the Row-specific Update query is performed
function afterUpdateRow(
    Table $table,
    Row $row,
    Update $update,
    PDOStatement $pdoStatement
) : void

// runs before the Row-specific Delete query is created
function beforeDeleteRow(Table $table, Row $row) : void

// runs after the Row-specific Delete query is created, but before it is performed
function modifyDeleteRow(Table $table, Row $row, Delete $delete) : void

// runs after the Row-specific Delete query is performed
function afterDeleteRow(
    Table $table,
    Row $row,
    Delete $delete,
    PDOStatement $pdoStatement
) : void
```

For example, when you call `Table::updateRow()`, these events run in this order:

- `beforeUpdateRow()`
- `modifyUpdate()`
- `modifyUpdateRow()`
- `afterUpdateRow()`

Note that merely calling `update()` to get a table-wide Update query will only
run the `modifyUpdate()` method, as it is not a row-specific interaction.

_TableEvents_ are the place to put behaviors such as setting `inserted_at` or
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
