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

    public function beforeInsert(Table $table, Row $row) : void
    {
    }

    public function modifyInsert(Table $table, Row $row, Insert $insert) : void
    {
    }

    public function afterInsert(Table $table, Row $row, Insert $insert, PDOStatement $pdoStatement) : void
    {
    }

    public function beforeUpdate(Table $table, Row $row) : void
    {
    }

    public function modifyUpdate(Table $table, Row $row, Update $update) : void
    {
    }

    public function afterUpdate(Table $table, Row $row, Update $update, PDOStatement $pdoStatement) : void
    {
    }

    public function beforeDelete(Table $table, Row $row) : void
    {
    }

    public function modifyDelete(Table $table, Row $row, Delete $delete) : void
    {
    }

    public function afterDelete(Table $table, Row $row, Delete $delete, PDOStatement $pdoStatement) : void
    {
    }
}
