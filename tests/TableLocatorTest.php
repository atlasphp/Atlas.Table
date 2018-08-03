<?php
namespace Atlas\Table;

use Atlas\Pdo\ConnectionLocator;
use Atlas\Query\QueryFactory;
use Atlas\Testing\DataSource\Employee\EmployeeTable;
use Atlas\Testing\DataSource\Employee\EmployeeTableEvents;
use Atlas\Testing\DataSourceFixture;

class TableLocatorTest extends \PHPUnit\Framework\TestCase
{
    protected function setUp()
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

        $this->expectException(Exception::CLASS);
        $this->expectExceptionMessage(
            'Foo not found in table locator.'
        );
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
