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

class PrimaryValueMissing extends Exception
{
    public function __construct(string $col)
    {
        $message = "Expected scalar value for primary key '$col', "
            . "value is missing instead.";

        parent::__construct($message);
    }
}
