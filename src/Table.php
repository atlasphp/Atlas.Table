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
        return $this->selectRow($this->select(), $primaryVal);
    }

    public function fetchRows(array $primaryVals) : array
    {
        return $this->selectRows($this->select(), $primaryVals);
    }

    public function select(array $whereEquals = []) : TableSelect
    {
        $select = $this->queryFactory->newSelect($this->getReadConnection());
        $select->setTable($this);
        $select->from($select->quoteIdentifier(static::NAME));
        $select->whereEquals($whereEquals);
        $this->tableEvents->modifySelect($this, $select);
        return $select;
    }

    public function selectRow(TableSelect $select, $primaryVal) : ?Row
    {
        $this->primaryKey->whereRow($select, $primaryVal);
        return $select->fetchRow();
    }

    public function selectRows(TableSelect $select, array $primaryVals) : array
    {
        $this->primaryKey->whereRows($select, $primaryVals);
        return $select->fetchRows();
    }

    public function insert() : Insert
    {
        $insert = $this->queryFactory->newInsert($this->getWriteConnection());
        $insert->into($insert->quoteIdentifier(static::NAME));
        $this->tableEvents->modifyInsert($this, $insert);
        return $insert;
    }

    public function insertRow(Row $row) : PDOStatement
    {
        $insert = $this->insertRowPrepare($row);
        return $this->insertRowPerform($row, $insert);
    }

    public function insertRowPrepare(Row $row) : Insert
    {
        $copy = $this->tableEvents->beforeInsertRow($this, $row);
        if ($copy === null) {
            $copy = $row->getArrayCopy();
        }

        $insert = $this->insert();
        $autoinc = static::AUTOINC_COLUMN;
        if ($autoinc !== null && ! isset($copy[$autoinc])) {
            unset($copy[$autoinc]);
        }
        $insert->columns($copy);

        $this->tableEvents->modifyInsertRow($this, $row, $insert);
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

        $this->tableEvents->afterInsertRow($this, $row, $insert, $pdoStatement);

        $row->init($row::INSERTED);
        return $pdoStatement;
    }

    public function update() : Update
    {
        $update = $this->queryFactory->newUpdate($this->getWriteConnection());
        $update->table($update->quoteIdentifier(static::NAME));
        $this->tableEvents->modifyUpdate($this, $update);
        return $update;
    }

    public function updateRow(Row $row) : ?PDOStatement
    {
        $update = $this->updateRowPrepare($row);
        return $this->updateRowPerform($row, $update);
    }

    public function updateRowPrepare(Row $row) : Update
    {
        $diff = $this->tableEvents->beforeUpdateRow($this, $row);
        if ($diff === null) {
            $diff = $row->getArrayDiff();
        }

        $update = $this->update();
        foreach (static::PRIMARY_KEY as $primaryCol) {
            if (array_key_exists($primaryCol, $diff)) {
                $message = "Primary key value for '$primaryCol' changed";
                throw new Exception($message);
            }
            $update->where("{$primaryCol} = ", $row->$primaryCol);
            unset($diff[$primaryCol]);
        }
        $update->columns($diff);

        $this->tableEvents->modifyUpdateRow($this, $row, $update);
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

        $this->tableEvents->afterUpdateRow($this, $row, $update, $pdoStatement);

        $row->init($row::UPDATED);
        return $pdoStatement;
    }

    public function delete() : Delete
    {
        $delete = $this->queryFactory->newDelete($this->getWriteConnection());
        $delete->from($delete->quoteIdentifier(static::NAME));
        $this->tableEvents->modifyDelete($this, $delete);
        return $delete;
    }

    public function deleteRow(Row $row) : ?PDOStatement
    {
        $delete = $this->deleteRowPrepare($row);
        return $this->deleteRowPerform($row, $delete);
    }

    public function deleteRowPrepare(Row $row) : Delete
    {
        $this->tableEvents->beforeDeleteRow($this, $row);

        $delete = $this->delete();
        foreach (static::PRIMARY_KEY as $primaryCol) {
            $delete->where("{$primaryCol} = ", $row->$primaryCol);
        }

        $this->tableEvents->modifyDeleteRow($this, $row, $delete);
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

        $this->tableEvents->afterDeleteRow($this, $row, $delete, $pdoStatement);
        $row->init($row::DELETED);
        return $pdoStatement;
    }

    public function newRow(array $cols = []) : Row
    {
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
