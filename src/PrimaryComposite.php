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

class PrimaryComposite
{
    protected $cols;

    public function __construct(array $cols)
    {
        $this->cols = $cols;
    }

    public function whereRow(TableSelect $select, array $primaryVal) : void
    {
        $condition = [];
        foreach ($this->cols as $col) {
            $this->assertCompositePart($primaryVal, $col);
            $qcol = $select->quoteIdentifier($col);
            $condition[] = "{$qcol} = " . $select->bindInline($primaryVal[$col]);
        }
        $select->where(implode(' AND ', $condition));
    }

    public function whereRows(TableSelect $select, array $primaryVals) : void
    {
        foreach ($primaryVals as $primaryVal) {
            $condition = [];
            foreach ($this->cols as $col) {
                $this->assertCompositePart($primaryVal, $col);
                $qcol = $select->quoteIdentifier($col);
                $condition[] = "{$qcol} = " . $select->bindInline($primaryVal[$col]);
            }
            $select->orWhere('(' . implode(' AND ', $condition) . ')');
        }
    }

    protected function assertCompositePart(array $primaryVal, string $col)
    {
        if (! isset($primaryVal[$col])) {
            throw Exception::primaryValueMissing($col);
        }

        if (! is_scalar($primaryVal[$col])) {
            throw Exception::primaryValueNotScalar($col, $primaryVal[$col]);
        }
    }
}
