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

class PrimarySimple
{
    protected string $col;

    public function __construct(array $cols)
    {
        $this->col = reset($cols);
    }

    public function whereRow(TableSelect $select, int|string $primaryVal) : void
    {
        $qcol = $select->quoteIdentifier($this->col);
        $select->where("{$qcol} = ", $primaryVal);
    }

    public function whereRows(TableSelect $select, array $primaryVals) : void
    {
        $qcol = $select->quoteIdentifier($this->col);
        $select->where("{$qcol} IN ", $primaryVals);
    }
}
