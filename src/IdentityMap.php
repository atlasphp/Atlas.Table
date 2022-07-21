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

use Atlas\Table\Exception;
use Atlas\Table\IdentityMap\CompositeIdentityMap;
use Atlas\Table\IdentityMap\SimpleIdentityMap;
use Atlas\Table\Row;
use Atlas\Table\Table;
use Atlas\Table\TableSelect;

/**
 * @todo add $table::ROW_CLASS type checks
 */
abstract class IdentityMap
{
    protected array $memory = [];

    public function __construct(protected Table $table)
    {
    }

    public function fetchRow(mixed $primaryVal, TableSelect $select = null) : ?Row
    {
        $serial = $this->getSerial($primaryVal);
        $memory = $this->getRowBySerial($serial);

        if ($memory !== null) {
            return $memory;
        }

        $select ??= $this->table->select();
        $row = $this->table->selectRow($select, $primaryVal);

        if ($row !== null) {
            $this->setRow($row);
        }

        return $row;
    }

    public function fetchRows(array $primaryVals, TableSelect $select = null) : array
    {
        $rows = [];

        // find identity-mapped rows, adding placeholders for missing rows
        foreach ($primaryVals as $primaryVal) {
            $serial = $this->getSerial($primaryVal);
            $memory = $this->getRowBySerial($serial);

            if ($memory === null) {
                $rows[$serial] = null;
                $missing[$serial] = $primaryVal;
            } else {
                $rows[$serial] = $memory;
            }
        }

        // early return if all rows are identity-mapped
        if (empty($missing)) {
            return array_values($rows);
        }

        $select ??= $this->table->select();

        // fetch rows missing from identity map
        foreach ($this->table->selectRows($select, $missing) as $row) {
            $serial = $this->getSerial($row);
            $rows[$serial] = $row;
            $this->setRow($row);
            unset($missing[$serial]);
        }

        // remove placeholders for unfetched rows
        foreach ($missing as $serial => $primaryVal) {
            unset($rows[$serial]);
        }

        return array_values($rows);
    }

    public function setRow(Row $row) : void
    {
        $serial = $this->getSerial($row);

        if (isset($this->memory[$serial])) {
            throw Exception::rowAlreadyIdentityMapped($row, $serial);
        }

        $this->memory[$serial] = $row;
    }

    public function memRow(Row $row) : Row
    {
        $serial = $this->getSerial($row);
        $memory = $this->getRowBySerial($serial);

        if ($memory === null) {
            $this->setRow($row);
            return $row;
        }

        return $memory;
    }

    public function getSerial(mixed $spec) : string
    {
        $array = ($spec instanceof Row)
            ? $this->getSerialArrayFromRow($spec)
            : $this->getSerialArray($spec);

        $sep = "|\x1F"; // a pipe, and ASCII 31 ("unit separator")
        return $sep . implode($sep, $array). $sep;
    }

    protected function getRowBySerial(string $serial) : ?Row
    {
        return $this->memory[$serial] ?? null;
    }

    abstract protected function getSerialArray(mixed $spec) : array;

    abstract protected function getSerialArrayFromRow(Row $row) : array;
}
