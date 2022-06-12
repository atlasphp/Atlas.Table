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
use Atlas\Query\Select;
use Atlas\Query\Update;
use Atlas\Table\Exception;
use PDOStatement;

abstract class Table
{
    public const NAME = '';

    public const COLUMNS = [];

    public const COLUMN_NAMES = [];

    public const COLUMN_DEFAULTS = [];

    public const PRIMARY_KEY = [];

    public const COMPOSITE_KEY = false;

    public const AUTOINC_COLUMN = null;

    public const AUTOINC_SEQUENCE = null;

    public const ROW_CLASS = '';

    public function __construct(
        protected ConnectionLocator $connectionLocator,
        protected TableEvents $tableEvents
    ) {
    }

    public function getReadConnection() : Connection
    {
        return $this->connectionLocator->getRead();
    }

    public function getWriteConnection() : Connection
    {
        return $this->connectionLocator->getWrite();
    }

    public function fetchRow(mixed $primaryVal) : ?Row
    {
        return $this->selectRow($this->select(), $primaryVal);
    }

    public function fetchRows(array $primaryVals) : array
    {
        return $this->selectRows($this->select(), $primaryVals);
    }

    public function select(array $whereEquals = []) : TableSelect
    {
        $class = get_class($this) . 'Select';
        $select = $class::new($this->getReadConnection(), $this, $whereEquals);
        $this->tableEvents->modifySelect($this, $select);
        return $select;
    }

    public function selectRow(TableSelect $select, mixed $primaryVal) : ?Row
    {
        if (static::COMPOSITE_KEY) {
            return $this->selectRowComposite($select, $primaryVal);
        }

        $qcol = $select->quoteIdentifier(static::PRIMARY_KEY[0]);
        $select->where("{$qcol} = ", $primaryVal);
        return $select->fetchRow();
    }

    protected function selectRowComposite(TableSelect $select, mixed $primaryVal) : ?Row
    {
        $primaryVal = (array) $primaryVal;
        $condition = [];

        foreach (static::PRIMARY_KEY as $col) {
            $this->assertCompositePart($primaryVal, $col);
            $qcol = $select->quoteIdentifier($col);
            $condition[] = "{$qcol} = " . $select->bindInline($primaryVal[$col]);
        }

        $select->where(implode(' AND ', $condition));
        return $select->fetchRow();
    }

    public function selectRows(TableSelect $select, array $primaryVals) : array
    {
        if (static::COMPOSITE_KEY) {
            return $this->selectRowsComposite($select, $primaryVals);
        }

        $qcol = $select->quoteIdentifier(static::PRIMARY_KEY[0]);
        $select->where("{$qcol} IN ", $primaryVals);
        return $select->fetchRows();
    }

    protected function selectRowsComposite(TableSelect $select, array $primaryVals) : array
    {
        foreach ($primaryVals as $primaryVal) {
            $condition = [];

            foreach (static::PRIMARY_KEY as $col) {
                $this->assertCompositePart($primaryVal, $col);
                $qcol = $select->quoteIdentifier($col);
                $condition[] = "{$qcol} = " . $select->bindInline($primaryVal[$col]);
            }

            $select->orWhere('(' . implode(' AND ', $condition) . ')');
        }

        return $select->fetchRows();
    }

    public function insert() : Insert
    {
        $insert = Insert::new($this->getWriteConnection());
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

        $row->setLastAction($row::INSERT);
        return $pdoStatement;
    }

    public function update() : Update
    {
        $update = Update::new($this->getWriteConnection());
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
                throw Exception::primaryValueChanged($primaryCol);
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

        if (empty(static::PRIMARY_KEY)) {
            throw Exception::cannotPerformWithoutPrimaryKey('update row', static::NAME);
        }

        $pdoStatement = $update->perform();

        $rowCount = $pdoStatement->rowCount();
        if ($rowCount != 1) {
            throw Exception::unexpectedRowCountAffected($rowCount);
        }

        $this->tableEvents->afterUpdateRow($this, $row, $update, $pdoStatement);

        $row->setLastAction($row::UPDATE);
        return $pdoStatement;
    }

    public function delete() : Delete
    {
        $delete = Delete::new($this->getWriteConnection());
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
        if ($row->getLastAction() === $row::DELETE) {
            return null;
        }

        if (empty(static::PRIMARY_KEY)) {
            throw Exception::cannotPerformWithoutPrimaryKey('delete row', static::NAME);
        }

        $pdoStatement = $delete->perform();

        $rowCount = $pdoStatement->rowCount();
        if ($rowCount != 1) {
            throw Exception::unexpectedRowCountAffected($rowCount);
        }

        $this->tableEvents->afterDeleteRow($this, $row, $delete, $pdoStatement);
        $row->setLastAction($row::DELETE);
        return $pdoStatement;
    }

    public function newRow(array $cols = []) : Row
    {
        $rowClass = static::ROW_CLASS;
        /** @var Row */
        $row = new $rowClass($cols);
        return $row;
    }

    public function newSelectedRow(array $cols) : Row
    {
        $row = $this->newRow($cols);
        $this->tableEvents->modifySelectedRow($this, $row);
        $row->setLastAction($row::SELECT);
        return $row;
    }

    protected function assertCompositePart(array $primaryVal, string $col) : void
    {
        if (! isset($primaryVal[$col])) {
            throw Exception::primaryValueMissing($col);
        }

        if (! is_scalar($primaryVal[$col])) {
            throw Exception::primaryValueNotScalar($col, $primaryVal[$col]);
        }
    }
}
