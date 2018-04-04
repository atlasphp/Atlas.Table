<?php
namespace Atlas\Table;

use Atlas\Pdo\ConnectionLocator;
use Atlas\Query\QueryFactory;
use Atlas\Testing\DataSource\Employee\EmployeeTable;
use Atlas\Testing\DataSource\Employee\EmployeeTableEvents;
use Atlas\Testing\DataSource\SqliteFixture;

class TableLocatorTest extends \PHPUnit\Framework\TestCase
{
    protected function setUp()
    {
        $connection = (new SqliteFixture())->exec();
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

        $this->expectException(
            'Atlas\Table\Exception',
            'Bar not found in table locator.'
        );
        $this->tableLocator->get('Foo');
    }
}
