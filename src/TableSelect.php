<?php
declare(strict_types=1);

/**
 *
 * This file is part of Atlas for PHP.
 *
 * @license http://opensource.org/licenses/MIT MIT
 *
 */
namespace Atlas\Table;

use Atlas\Pdo\Connection;
use Atlas\Query\Bind;
use Atlas\Query\Select;

class TableSelect extends Select
{
    protected $table;

    public function __construct(Connection $connection, Bind $bind, Table $table)
    {
        parent::__construct($connection, $bind);
        $this->table = $table;
    }

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

    public function fetchCount(string $col = '*') : int
    {
        $select = clone $this;
        $select->resetColumns()
            ->resetLimit()
            ->columns("COUNT({$col})");

        return (int) $this->table->getReadConnection()->fetchValue(
            $select->getStatement(),
            $select->getBindValues()
        );
    }
}
