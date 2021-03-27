<?php
/**
 *
 * This file is part of Atlas for PHP.
 *
 * @license http://opensource.org/licenses/MIT MIT
 *
 */
declare(strict_types=1);

namespace Atlas\Table;

use Atlas\Pdo\ConnectionLocator;

class TableLocator
{
    static public function new(mixed ...$args) : static
    {
        return new static(
            ConnectionLocator::new(...$args)
        );
    }

    protected array $instances = [];

    public function __construct(
        protected ConnectionLocator $connectionLocator,
        protected mixed /* callable */ $factory = null
    ) {
        if ($this->factory === null) {
            $this->factory = function ($class) {
                return new $class();
            };
        }
    }

    public function has(string $tableClass) : bool
    {
        return class_exists($tableClass)
            && is_subclass_of($tableClass, Table::CLASS);
    }

    public function get(string $tableClass) : Table
    {
        if (! $this->has($tableClass)) {
            throw Exception::tableNotFound($tableClass);
        }

        if (! isset($this->instances[$tableClass])) {
            $this->instances[$tableClass] = $this->newTable($tableClass);
        }

        return $this->instances[$tableClass];
    }

    public function getConnectionLocator() : ConnectionLocator
    {
        return $this->connectionLocator;
    }

    protected function newTable(string $tableClass) : Table
    {
        return new $tableClass(
            $this->connectionLocator,
            ($this->factory)($tableClass . 'Events')
        );
    }
}
