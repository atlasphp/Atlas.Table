<?php
namespace Atlas\Table;

use Atlas\Pdo\ConnectionLocator;
use Atlas\Testing\DataSourceFixture;
use Atlas\Testing\DataSource\Employee\EmployeeTable;
use Atlas\Testing\DataSource\Employee\EmployeeTableEvents;

class TableLocatorTest extends \PHPUnit\Framework\TestCase
{
    protected function setUp() : void
    {
        $connection = (new DataSourceFixture())->exec();
        $this->tableLocator = TableLocator::new($connection);
    }

    public function testHas()
    {
        $this->assertTrue($this->tableLocator->has(EmployeeTable::CLASS));
        $this->assertFalse($this->tableLocator->has('Foo'));
    }

    public function testGet()
    {
        $actual = $this->tableLocator->get(EmployeeTable::CLASS);
        $this->assertInstanceOf(EmployeeTable::CLASS, $actual);

        $again = $this->tableLocator->get(EmployeeTable::CLASS);
        $this->assertSame($actual, $again);

        $this->expectException(Exception\TableClassMissing::CLASS);
        $this->expectExceptionMessage("Table class 'Foo' does not exist, or is not a Table.");
        $this->tableLocator->get('Foo');
    }

    public function testGetConnectionLocator()
    {
        $this->assertInstanceOf(
            ConnectionLocator::CLASS,
            $this->tableLocator->getConnectionLocator()
        );
    }
}
