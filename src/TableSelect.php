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

use Atlas\Query\Select;

abstract class TableSelect extends Select
{
    static public function new(mixed $arg, mixed ...$args) : static
    {
        $table = array_pop($args);
        $select = parent::new($arg, ...$args);
        $select->table = $table;
        return $select;
    }

    protected Table $table;

    public function fetchRow() : ?Row
    {
        if (! $this->columns->hasAny()) {
            $this->columns("*");
        }

        $cols = $this->fetchOne();
        if ($cols === null) {
            return null;
        }

        return $this->table->newSelectedRow($cols);
    }

    public function fetchRows() : array
    {
        if (! $this->columns->hasAny()) {
            $this->columns("*");
        }

        $rows = [];
        foreach ($this->yieldAll() as $cols) {
            $rows[] = $this->table->newSelectedRow($cols);
        }

        return $rows;
    }

    public function fetchCount(string $column = '*') : int
    {
        $select = clone $this;
        $select
            ->resetColumns()
            ->resetLimit()
            ->columns("COUNT({$column})");

        return (int) $this->table->getReadConnection()->fetchValue(
            $select->getStatement(),
            $select->getBindValues()
        );
    }
}
