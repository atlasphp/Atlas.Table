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

class PrimaryValueChanged extends Exception
{
    public function __construct(string $col, mixed $old, mixed $new)
    {
        $old = var_export($old, true);
        $new = var_export($new, true);
        $message = "Primary key value for '$col' changed from {$old} to {$new}.";

        parent::__construct($message);
    }
}
