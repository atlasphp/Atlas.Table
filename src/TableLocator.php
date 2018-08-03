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
    protected $connectionLocator;

    protected $tableQueryFactory;

    protected $factory;

    protected $tables = [];

    public static function new(...$args) : TableLocator
    {
        return new TableLocator(
            ConnectionLocator::new(...$args),
            new TableQueryFactory()
        );
    }

    public function __construct(
        ConnectionLocator $connectionLocator,
        TableQueryFactory $tableQueryFactory,
        callable $factory = null
    ) {
        $this->connectionLocator = $connectionLocator;
        $this->tableQueryFactory = $tableQueryFactory;
        $this->factory = $factory;
        if ($this->factory === null) {
            $this->factory = function ($class) {
                return new $class();
            };
        }
    }

    public function has(string $tableClass) : bool
    {
        return class_exists($tableClass) && is_subclass_of($tableClass, Table::CLASS);
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
            $this->tableQueryFactory->newQueryFactory($tableClass),
            ($this->factory)($tableClass . 'Events')
        );
    }
}
