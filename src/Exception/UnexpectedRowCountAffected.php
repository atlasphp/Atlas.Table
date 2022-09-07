<?php
/**
 *
 * This file is part of Atlas for PHP.
 *
 * @license http://opensource.org/licenses/MIT MIT
 *
 */
declare(strict_types=1);

namespace Atlas\Table\Exception;

use Atlas\Table\Exception;

class UnexpectedRowCountAffected extends Exception
{
    public function __construct(int $count)
    {
        parent::__construct("Expected 1 row affected, actual {$count}.");
    }
}
