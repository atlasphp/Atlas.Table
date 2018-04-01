<?php
namespace Atlas\Table;

use Atlas\Pdo\ConnectionLocator;
use Atlas\Query\QueryFactory;
use Atlas\Testing\DataSource\Employee\EmployeeTable;
use Atlas\Testing\DataSource\Employee\EmployeeTableEvents;

class TableLocatorTest extends \PHPUnit\Framework\TestCase
{
    protected function setUp()
    {
        $this->tableLocator = new TableLocator([
            EmployeeTable::CLASS => function () {
                return new EmployeeTable(
                    new ConnectionLocator(),
                    new QueryFactory(),
                    new EmployeeTableEvents()
                );
            },
        ]);
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
