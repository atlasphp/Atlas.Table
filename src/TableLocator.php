<?php
declare(strict_types=1);

/**
 *
 * This file is part of Atlas for PHP.
 *
 * @license http://opensource.org/licenses/MIT MIT
 *
 */
namespace Atlas\Table;

use Atlas\Table\Exception;

class TableLocator
{
    protected $factories = [];

    protected $instances = [];

    public function __construct(array $factories)
    {
        $this->factories = $factories;
    }

    public function has(string $class) : bool
    {
        return isset($this->factories[$class]);
    }

    public function get(string $class) : Table
    {
        if (! $this->has($class)) {
            throw Exception::tableNotFound($class);
        }

        if (! isset($this->instances[$class])) {
            $this->instances[$class] = call_user_func($this->factories[$class]);
        }

        return $this->instances[$class];
    }
}
