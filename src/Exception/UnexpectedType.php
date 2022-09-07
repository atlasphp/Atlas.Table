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

class UnexpectedType extends Exception
{
    public function __construct(string $label, string $expect, string $actual)
    {
        $message = "Expected {$label} of type {$expect}, got {$actual} instead.";
        parent::__construct($message);
    }
}
