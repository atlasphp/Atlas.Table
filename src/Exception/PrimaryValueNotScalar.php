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

class PrimaryValueNotScalar extends Exception
{
    public function __construct(string $col, mixed $val)
    {
        $message = "Expected scalar value for primary key '{$col}', "
            . "got " . gettype($val) . " instead.";

        parent::__construct($message);
    }
}
