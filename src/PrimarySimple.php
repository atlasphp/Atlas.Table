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
    protected $col;

    public function __construct(string $col)
    {
        $this->col = $col;
    }

    public function whereRow(TableSelect $select, $primaryVal) : void
    {
        $select->where("{$this->col} = ", $primaryVal);
    }

    public function whereRows(TableSelect $select, array $primaryVals) : void
    {
        $select->where("{$this->col} IN ", $primaryVals);
    }
}
