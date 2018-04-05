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
use Atlas\Query\Select;

class QueryFactory extends \Atlas\Query\QueryFactory
{
    public function newSelect(Connection $connection, ...$args) : Select
    {
        return $this->newQuery(TableSelect::CLASS, $connection, $args);
    }
}
