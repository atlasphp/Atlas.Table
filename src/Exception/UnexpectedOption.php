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

class UnexpectedOption extends Exception
{
    public function __construct(string $option, array $options)
    {
        $message = "Expected one of '" . implode("','", $options)
            . "'; got '{$option}' instead.";
        parent::__construct($message);
    }
}
