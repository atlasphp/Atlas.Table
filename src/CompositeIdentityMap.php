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

class CompositeIdentityMap extends IdentityMap
{
    protected function getSerialArray(mixed $primaryVal) : array
    {
        $primaryVal = (array) $primaryVal;
        $serial = [];

        foreach ($this->table::PRIMARY_KEY as $col) {
            $serial[$col] = $primaryVal[$col] ?? null;

            if (! is_scalar($serial[$col])) {
                throw new Exception\PrimaryValueNotScalar($col, $serial[$col]);
            }
        }

        return $serial;
    }

    protected function getSerialArrayFromRow(Row $row) : array
    {
        $this->assertRow($row);
        $serial = [];

        foreach ($this->table::PRIMARY_KEY as $col) {
            $serial[$col] = $row->$col ?? null;

            if (! is_scalar($serial[$col])) {
                throw new Exception\PrimaryValueNotScalar($col, $serial[$col]);
            }
        }

        return $serial;
    }
}
