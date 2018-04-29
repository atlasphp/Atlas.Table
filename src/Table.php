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

use Atlas\Pdo\Connection;
use Atlas\Pdo\ConnectionLocator;
use Atlas\Query\Bind;
use Atlas\Query\Delete;
use Atlas\Query\Insert;
use Atlas\Query\QueryFactory;
use Atlas\Query\Select;
use Atlas\Query\Update;
use Atlas\Table\Exception;
use PDOStatement;

abstract class Table
{
    const NAME = '';

    const COLUMNS = [];

    const COLUMN_NAMES = [];

    const COLUMN_DEFAULTS = [];

    const PRIMARY_KEY = [];

    const AUTOINC_COLUMN = null;

    const AUTOINC_SEQUENCE = null;

    protected $queryFactory;

    protected $rowClass;

    protected $connectionLocator;

    protected $tableEvents;

    protected $primaryKey;

    public function __construct(
        ConnectionLocator $connectionLocator,
        QueryFactory $queryFactory,
        TableEvents $tableEvents
    ) {
        $this->connectionLocator = $connectionLocator;
        $this->queryFactory = $queryFactory;
        $this->tableEvents = $tableEvents;
        $this->rowClass = substr(static::CLASS, 0, -5) . 'Row';
        if (count($this::PRIMARY_KEY) == 1) {
            $this->primaryKey = new PrimarySimple($this::PRIMARY_KEY[0]);
        } else {
            $this->primaryKey = new PrimaryComposite($this::PRIMARY_KEY);
        }
    }

    public function getReadConnection() : Connection
    {
        return $this->connectionLocator->getRead();
    }

    public function getWriteConnection() : Connection
    {
        return $this->connectionLocator->getWrite();
    }

    public function fetchRow($primaryVal) : ?Row
    {
        $select = $this->select();
        $this->primaryKey->whereRow($select, $primaryVal);
        return $select->fetchRow();
    }

    public function fetchRows(array $primaryVals) : array
    {
        $select = $this->select();
        $this->primaryKey->whereRows($select, $primaryVals);
        return $select->fetchRows();
    }

    public function select(array $whereEquals = []) : TableSelect
    {
        $select = $this->queryFactory->newSelect($this->getReadConnection());
        $select->setTable($this);
        $select->from(static::NAME);
        foreach ($whereEquals as $key => $val) {
            $this->selectWhere($select, $key, $val);
        }

        $this->tableEvents->modifySelect($this, $select);
        return $select;
    }

    protected function selectWhere(TableSelect $select, $key, $val) : void
    {
        if (is_numeric($key)) {
            $select->where($val);
            return;
        }

        if ($val === null) {
            $select->where("{$key} IS NULL");
            return;
        }

        if (is_array($val)) {
            $select->where("{$key} IN ", $val);
            return;
        }

        $select->where("{$key} = ", $val);
    }

    public function insert() : Insert
    {
        $insert = $this->queryFactory->newInsert($this->getWriteConnection());
        $insert->into(static::NAME);
        return $insert;
    }

    public function insertRow(Row $row) : PDOStatement
    {
        $insert = $this->insertRowPrepare($row);
        return $this->insertRowPerform($row, $insert);
    }

    public function insertRowPrepare(Row $row) : Insert
    {
        $this->tableEvents->beforeInsert($this, $row);

        $insert = $this->insert();
        $cols = $row->getArrayCopy();
        if (static::AUTOINC_COLUMN !== null) {
            unset($cols[static::AUTOINC_COLUMN]);
        }
        $insert->columns($cols);

        $this->tableEvents->modifyInsert($this, $row, $insert);
        return $insert;
    }

    public function insertRowPerform(Row $row, Insert $insert) : PDOStatement
    {
        $pdoStatement = $insert->perform();

        $rowCount = $pdoStatement->rowCount();
        if ($rowCount != 1) {
            throw Exception::unexpectedRowCountAffected($rowCount);
        }

        $autoinc = static::AUTOINC_COLUMN;
        if ($autoinc !== null) {
            $row->$autoinc = $insert->getLastInsertId(static::AUTOINC_SEQUENCE);
        }

        $this->tableEvents->afterInsert($this, $row, $insert, $pdoStatement);

        $row->init($row::INSERTED);
        return $pdoStatement;
    }

    public function update() : Update
    {
        $update = $this->queryFactory->newUpdate($this->getWriteConnection());
        $update->table(static::NAME);
        return $update;
    }

    public function updateRow(Row $row) : ?PDOStatement
    {
        $update = $this->updateRowPrepare($row);
        return $this->updateRowPerform($row, $update);
    }

    public function updateRowPrepare(Row $row) : Update
    {
        $this->tableEvents->beforeUpdate($this, $row);

        $update = $this->update();
        $diff = $row->getArrayDiff();
        foreach (static::PRIMARY_KEY as $primaryCol) {
            if (array_key_exists($primaryCol, $diff)) {
                $message = "Primary key value for '$primaryCol' changed";
                throw new Exception($message);
            }
            $update->where("{$primaryCol} = ", $row->$primaryCol);
            unset($diff[$primaryCol]);
        }
        $update->columns($diff);

        $this->tableEvents->modifyUpdate($this, $row, $update);
        return $update;
    }

    public function updateRowPerform(Row $row, Update $update) : ?PDOStatement
    {
        if (! $update->hasColumns()) {
            return null;
        }

        $pdoStatement = $update->perform();

        $rowCount = $pdoStatement->rowCount();
        if ($rowCount != 1) {
            throw Exception::unexpectedRowCountAffected($rowCount);
        }

        $this->tableEvents->afterUpdate($this, $row, $update, $pdoStatement);

        $row->init($row::UPDATED);
        return $pdoStatement;
    }

    public function delete() : Delete
    {
        $delete = $this->queryFactory->newDelete($this->getWriteConnection());
        $delete->from(static::NAME);
        return $delete;
    }

    public function deleteRow(Row $row) : ?PDOStatement
    {
        $delete = $this->deleteRowPrepare($row);
        return $this->deleteRowPerform($row, $delete);
    }

    public function deleteRowPrepare(Row $row) : Delete
    {
        $this->tableEvents->beforeDelete($this, $row);

        $delete = $this->delete();
        foreach (static::PRIMARY_KEY as $primaryCol) {
            $delete->where("{$primaryCol} = ", $row->$primaryCol);
        }

        $this->tableEvents->modifyDelete($this, $row, $delete);
        return $delete;
    }

    public function deleteRowPerform(Row $row, Delete $delete) : ?PDOStatement
    {
        if ($row->getStatus() === $row::DELETED) {
            return null;
        }

        $pdoStatement = $delete->perform();

        $rowCount = $pdoStatement->rowCount();
        if ($rowCount != 1) {
            throw Exception::unexpectedRowCountAffected($rowCount);
        }

        $this->tableEvents->afterDelete($this, $row, $delete, $pdoStatement);
        $row->init($row::DELETED);
        return $pdoStatement;
    }

    public function newRow(array $cols = []) : Row
    {
        $colNames = static::COLUMN_NAMES;
        foreach ($cols as $col => $val) {
            if (! in_array($col, $colNames)) {
                unset($cols[$col]);
            }
        }

        $rowClass = $this->rowClass;
        return new $rowClass($cols);
    }

    public function newSelectedRow(array $cols) : Row
    {
        $row = $this->newRow($cols);
        $this->tableEvents->modifySelectedRow($this, $row);
        $row->init($row::SELECTED);
        return $row;
    }
}
