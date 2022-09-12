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

class TableClassMissing extends Exception
{
    public function __construct(string $class)
    {
        parent::__construct("Table class '{$class}' does not exist, or is not a Table.");
    }
}
