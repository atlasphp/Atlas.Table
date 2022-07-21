<?php
/**
 *
 * This file is part of Atlas for PHP.
 *
 * @license http://opensource.org/licenses/MIT MIT
 *
 */
declare(strict_types=1);

namespace Atlas\Table\IdentityMap;

use Atlas\Table\Exception;
use Atlas\Table\IdentityMap;
use Atlas\Table\Row;

class CompositeIdentityMap extends IdentityMap
{
    protected function getSerialArray(mixed $primaryVal) : array
    {
        $primaryVal = (array) $primaryVal;
        $serial = [];

        foreach ($this->table::PRIMARY_KEY as $col) {
            $serial[$col] = $primaryVal[$col] ?? null;

            if (! is_scalar($serial[$col])) {
                throw Exception::primaryValueNotScalar($col, $serial[$col]);
            }
        }

        return $serial;
    }

    protected function getSerialArrayFromRow(Row $row) : array
    {
        $serial = [];

        foreach ($this->table::PRIMARY_KEY as $col) {
            $serial[$col] = $row->$col ?? null;

            if (! is_scalar($serial[$col])) {
                throw Exception::primaryValueNotScalar($col, $serial[$col]);
            }
        }

        return $serial;
    }
}
