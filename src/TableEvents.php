<?php
/**
 *
 * This file is part of Atlas for PHP.
 *
 * @license http://opensource.org/licenses/MIT MIT
 *
 */
declare(strict_types=1);

namespace Atlas\Table;

use Atlas\Query\Delete;
use Atlas\Query\Insert;
use Atlas\Query\Select;
use Atlas\Query\Update;
use PDOStatement;

abstract class TableEvents
{
    public function modifySelect(Table $table, Select $select) : void
    {
    }

    public function modifySelectedRow(Table $table, Row $row) : void
    {
    }

    public function beforeInsertRow(Table $table, Row $row) : ?array
    {
        return null;
    }

    public function modifyInsertRow(Table $table, Row $row, Insert $insert) : void
    {
    }

    public function afterInsertRow(
        Table $table,
        Row $row,
        Insert $insert,
        PDOStatement $pdoStatement
    ) : void
    {
    }

    public function beforeUpdateRow(Table $table, Row $row) : ?array
    {
        return null;
    }

    public function modifyUpdateRow(Table $table, Row $row, Update $update) : void
    {
    }

    public function afterUpdateRow(
        Table $table,
        Row $row,
        Update $update,
        PDOStatement $pdoStatement
    ) : void
    {
    }

    public function beforeDeleteRow(Table $table, Row $row) : void
    {
    }

    public function modifyDeleteRow(Table $table, Row $row, Delete $delete) : void
    {
    }

    public function afterDeleteRow(
        Table $table,
        Row $row,
        Delete $delete,
        PDOStatement $pdoStatement
    ) : void
    {
    }

    public function modifyInsert(Table $table, Insert $insert) : void
    {
    }

    public function modifyUpdate(Table $table, Update $update) : void
    {
    }

    public function modifyDelete(Table $table, Delete $delete) : void
    {
    }
}
