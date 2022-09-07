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
use Atlas\Table\Row;

class RowAlreadyIdentityMapped extends Exception
{
    public function __construct(Row $row, string $serial)
    {
        $class = get_class($row);
        parent::__construct("{$class} with serial {$serial} already exists in IdentityMap.");
    }
}
