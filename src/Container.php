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

use Atlas\Pdo\Connection;
use Atlas\Pdo\ConnectionLocator;
use Atlas\Query\QueryFactory;
use PDO;

class Container
{
    protected $connectionLocator;

    protected $queryFactory;

    protected $tables = [];

    protected $factory;

    public function __construct(...$args)
    {
        $this->connectionLocator = $this->newConnectionLocator($args);
        $this->queryFactory = new QueryFactory();
        $this->factory = function ($class) {
            return new $class();
        };
    }

    public function setFactory(callable $factory)
    {
        $this->factory = $factory;
    }

    public function setTables(array $tableClasses) : void
    {
        foreach ($tableClasses as $tableClass) {
            $this->setTable($tableClass);
        }
    }

    public function setTable(string $tableClass) : void
    {
        if (! class_exists($tableClass)) {
            throw Exception::classDoesNotExist($tableClass);
        }

        $this->tables[$tableClass] = function () use ($tableClass) {
            return new $tableClass(
                $this->connectionLocator,
                $this->queryFactory,
                ($this->factory)($tableClass . 'Events')
            );
        };
    }

    public function newTableLocator() : TableLocator
    {
        return new TableLocator($this->tables);
    }

    public function getConnectionLocator() : ConnectionLocator
    {
        return $this->connectionLocator;
    }

    protected function newConnectionLocator(array $args) : ConnectionLocator
    {
        if ($args[0] instanceof ConnectionLocator) {
            return $args[0];
        }

        if ($args[0] instanceof Connection) {
            $default = function () use ($args) {
                return $args[0];
            };
        } elseif ($args[0] instanceof PDO) {
            $default = function () use ($args) {
                return new Connection($args[0]);
            };
        } else {
            $default = function () use ($args) {
                return new Connection(new PDO(...$args));
            };
        }

        return new ConnectionLocator($default);
    }
}
