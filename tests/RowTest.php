<?php
namespace Atlas\Table;

use Atlas\Table\Exception;
use Atlas\Testing\DataSource\Employee\EmployeeRow;

class RowTest extends \PHPUnit\Framework\TestCase
{
    public function testGetMissingCol()
    {
        $row = new EmployeeRow();
        $this->expectException(Exception::CLASS);
        $row->no_such_col;
    }

    public function testSetMissingCol()
    {
        $row = new EmployeeRow();
        $this->expectException(Exception::CLASS);
        $row->no_such_col = 'name';
    }

    public function testIsset()
    {
        $row = new EmployeeRow();
        $this->assertFalse(isset($row->id));
        $row->id = 1;
        $this->assertTrue(isset($row->id));
    }

    public function testUnset()
    {
        $row = new EmployeeRow(['name' => 'bar']);
        $this->assertSame('bar', $row->name);
        unset($row->name);
        $this->assertNull($row->name);
    }

    public function testUnsetMissingCol()
    {
        $row = new EmployeeRow();
        $this->expectException(Exception::CLASS);
        unset($row->no_such_col);
    }

    public function testInvalidModification_object()
    {
        $row = new EmployeeRow();
        $this->expectException(Exception::CLASS);
        $this->expectExceptionMessage(
            'Expected type scalar or null; got stdClass instead.'
        );
        $row->name = (object) [];
    }

    public function testInvalidModification_other()
    {
        $row = new EmployeeRow();
        $this->expectException(Exception::CLASS);
        $this->expectExceptionMessage(
            'Expected type scalar or null; got array instead.'
        );
        $row->name = [];
    }

    public function testSet()
    {
        $row = new EmployeeRow(['id' => '1', 'name' => 'bar']);
        $row->set(['name' => 'baz', 'irk' => 'gir']);
        $this->assertSame('baz', $row->name);
        $this->assertFalse($row->has('irk'));
    }

    public function testJsonSerialize()
    {
        $row = new EmployeeRow();
        $actual = json_encode($row);
        $expect = '{"id":null,"name":null,"building":null,"floor":null}';
        $this->assertSame($expect, $actual);
    }
}
