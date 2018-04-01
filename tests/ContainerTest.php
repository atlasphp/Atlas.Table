<?php
namespace Atlas\Table;

use Atlas\Pdo\Connection;
use Atlas\Pdo\ConnectionLocator;
use Atlas\Table\Exception;
use Atlas\Testing\DataSource\Employee\EmployeeTable;
use PDO;

class ContainerTest extends \PHPUnit\Framework\TestCase
{
    protected $container;

    protected function setUp()
    {
        $this->container = new Container('sqlite::memory:');
    }

    public function test()
    {
        $this->container->setTables([
            EmployeeTable::CLASS,
        ]);

        $tableLocator = $this->container->newTableLocator();
        $this->assertInstanceOf(EmployeeTable::CLASS, $tableLocator->get(EmployeeTable::CLASS));
    }

    public function testSetTable_noSuchTable()
    {
        $this->expectException(
            Exception::CLASS,
            'FooTable does not exist'
        );
        $this->container->setTable('FooTable');
    }

    public function testConstructWithConnectionLocator()
    {
        $locator = new ConnectionLocator(function() {
            return new Connection(new PDO('sqlite::memory:'));
        });
        $tableContainer = new Container($locator);

        $this->assertEquals($locator, $tableContainer->getConnectionLocator());
    }

    public function testConstructWithConnection()
    {
        $connection = new Connection(new PDO('sqlite::memory:'));
        $tableContainer = new Container($connection);
        $actual = $tableContainer->getConnectionLocator()->getDefault();
        $this->assertSame($connection, $actual);
    }

    public function testConstructWithPdo()
    {
        $pdo = new Pdo('sqlite::memory:');
        $tableContainer = new Container($pdo);
        $actual = $tableContainer->getConnectionLocator()->getDefault()->getPdo();
        $this->assertSame($pdo, $actual);
    }
}
