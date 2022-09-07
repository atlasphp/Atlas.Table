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

class ImmutableAfterDeleted extends Exception
{
    public function __construct(string $class, string $property)
    {
        $classProp = "{$class}::\${$property}";
        return new Exception("{$classProp} is immutable after Row is deleted.");
    }
}
