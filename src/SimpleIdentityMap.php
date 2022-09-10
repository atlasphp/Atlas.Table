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

class SimpleIdentityMap extends IdentityMap
{
    protected function getSerialArray(mixed $primaryVal) : array
    {
        $col = $this->table::PRIMARY_KEY[0];
        $serial = [
            $col => $primaryVal,
        ];

        if (! is_scalar($serial[$col])) {
            throw new Exception\PrimaryValueNotScalar($col, $serial[$col]);
        }

        return $serial;
    }

    protected function getSerialArrayFromRow(Row $row) : array
    {
        $this->assertRow($row);
        $col = $this->table::PRIMARY_KEY[0];
        $serial = [
            $col => $row->{$col},
        ];

        if (! is_scalar($serial[$col])) {
            throw new Exception\PrimaryValueNotScalar($col, $serial[$col]);
        }

        return $serial;
    }
}