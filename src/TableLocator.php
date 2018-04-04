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

use Atlas\Query\QueryFactory;
use Atlas\Pdo\ConnectionLocator;

class TableLocator
{
    protected $connectionLocator;

    protected $queryFactory;

    protected $factory;

    protected $tables = [];

    public static function new(...$args) : TableLocator
    {
        return new static(
            ConnectionLocator::new(...$args),
            new QueryFactory()
        );
    }

    public function __construct(
        ConnectionLocator $connectionLocator,
        QueryFactory $queryFactory,
        callable $factory = null
    ) {
        $this->connectionLocator = $connectionLocator;
        $this->queryFactory = $queryFactory;
        $this->factory = $factory;
        if ($this->factory === null) {
            $this->factory = function ($class) {
                return new $class();
            };
        }
    }

    public function has(string $class) : bool
    {
        return class_exists($class) && is_subclass_of($class, Table::CLASS);
    }

    public function get(string $class) : Table
    {
        if (! $this->has($class)) {
            throw Exception::tableNotFound($class);
        }

        if (! isset($this->instances[$class])) {
            $this->instances[$class] = $this->newTable($class);
        }

        return $this->instances[$class];
    }

    protected function newTable($class) : Table
    {
        return new $class(
            $this->connectionLocator,
            $this->queryFactory,
            ($this->factory)($class . 'Events')
        );
    }
}
