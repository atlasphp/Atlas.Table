<?php
namespace Atlas\Table;

use Atlas\Table\Container;
use Atlas\Testing\DataSourceFixture;
use Atlas\Testing\DataSource\Employee\EmployeeRow;
use Atlas\Testing\DataSource\Employee\EmployeeTable;

class TableSelectTest extends \PHPUnit\Framework\TestCase
{
    protected $select;

    protected $table;

    protected function setUp() : void
    {
        $connection = (new DataSourceFixture())->exec();
        $this->table = TableLocator::new($connection)->get(EmployeeTable::CLASS);
        $this->select = $this->table->select();
    }

    public function testFetchRow()
    {
        $expect = [
            'id' => '1',
            'name' => 'Anna',
            'building' => '1',
            'floor' => '1',
        ];

        // success
        $actual = $this->select->where('id = ', '1')->fetchRow();
        $this->assertInstanceOf(EmployeeRow::CLASS, $actual);
        $this->assertEquals($expect, $actual->getArrayCopy());

        // failure
        $actual = $this->select->where('id = ', '-1')->fetchRow();
        $this->assertNull($actual);
    }

    public function testFetchRows()
    {
        $expect = [
            [
                'id' => '1',
                'name' => 'Anna',
                'building' => '1',
                'floor' => '1',
            ],
            [
                'id' => '2',
                'name' => 'Betty',
                'building' => '1',
                'floor' => '2',
            ],
            [
                'id' => '3',
                'name' => 'Clara',
                'building' => '1',
                'floor' => '3',
            ],
        ];

        // success
        $actual = $this->select->where('id IN ', [1, 2, 3])->fetchRows();
        $this->assertCount(3, $actual);
        $this->assertEquals($expect[0], $actual[0]->getArrayCopy());
        $this->assertEquals($expect[1], $actual[1]->getArrayCopy());
        $this->assertEquals($expect[2], $actual[2]->getArrayCopy());

        // failure
        $actual = $this->select->where('id IN ', [997, 998, 999])->fetchRows();
        $this->assertEquals([], $actual);
    }

    public function testFetchCount()
    {
        $actual = $this->select->limit(6)->fetchRows();
        $this->assertCount(6, $actual);

        $actual = $this->select->fetchCount();
        $this->assertEquals(12, $actual);
    }
}
