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
use Atlas\Query\QueryFactory;
use Atlas\Query\Select;

class TableQueryFactory extends QueryFactory
{
    public function newSelect(Connection $connection) : Select
    {
        return new TableSelect($connection, $this->newBind());
    }
}
