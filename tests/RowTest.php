<?php
namespace Atlas\Table;

use Atlas\Table\Exception;
use Atlas\Table\DataSource\Employee\EmployeeRow;

class RowTest extends \PHPUnit\Framework\TestCase
{
    public function testConstructWithExtraKeys()
    {
        $row = new EmployeeRow(['no_such_col' => 'foo']);
        $this->assertInstanceOf(EmployeeRow::CLASS, $row);
    }

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

    public function testSetWhenDeleted()
    {
        $row = new EmployeeRow();
        $row->setLastAction($row::DELETE);
        $this->expectException(Exception::CLASS);
        $row->id = 'foo';
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

    public function testUnsetWhenDeleted()
    {
        $row = new EmployeeRow();
        $row->setLastAction($row::DELETE);
        $this->expectException(Exception::CLASS);
        unset($row->name);
    }

    public function testUnsetMissingCol()
    {
        $row = new EmployeeRow();
        $this->expectException(Exception::CLASS);
        unset($row->no_such_col);
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

    public function testActionTracking()
    {
        $row = new EmployeeRow();

        // new row; needs insert
        $this->assertSame(null, $row->getLastAction());
        $this->assertSame($row::INSERT, $row->getNextAction());

        // mark for deletion on new row; no next action
        $row->setDelete(true);
        $this->assertSame(null, $row->getNextAction());

        // unmark for deletion; back to insert
        $row->setDelete(false);
        $this->assertSame($row::INSERT, $row->getNextAction());

        // selected row, no changes: no action
        $row->setLastAction($row::SELECT);
        $this->assertSame($row::SELECT, $row->getLastAction());
        $this->assertSame(null, $row->getNextAction());

        // change the name; needs update
        $row->name = 'New Name';
        $this->assertSame($row::SELECT, $row->getLastAction());
        $this->assertSame($row::UPDATE, $row->getNextAction());

        // revert the name; no next action
        $row->name = null;
        $this->assertSame($row::SELECT, $row->getLastAction());
        $this->assertSame(null, $row->getNextAction());

        // mark fot deletion
        $row->setDelete(true);
        $this->assertSame($row::DELETE, $row->getNextAction());

        $this->expectException(Exception::CLASS);
        $row->setLastAction('NO_SUCH_STATUS');
    }

    public function testGetArray()
    {
        $init = [
            'id' => 1,
            'name' => 'foo',
            'building' => 'bar',
            'floor' => 2,
        ];

        $row = new EmployeeRow($init);
        $row->setLastAction($row::SELECT);

        $row->name = 'baz';
        $this->assertSame($init, $row->getArrayInit());

        $copy = $init;
        $copy['name'] = 'baz';
        $this->assertSame($copy, $row->getArrayCopy());

        $diff = ['name' => 'baz'];
        $this->assertSame($diff, $row->getArrayDiff());
    }

    public function testIterator()
    {
        $init = [
            'id' => 1,
            'name' => 'foo',
            'building' => 'bar',
            'floor' => 2,
        ];

        $row = new EmployeeRow($init);

        foreach ($row as $key => $val) {
            $this->assertSame($init[$key], $val);
            unset($init[$key]);
        }

        $this->assertEmpty($init);
    }

    public function testIsModified_numericToBool()
    {
        $init = [
            'id' => 1,
            'name' => 'foo',
            'building' => 'bar',
            'floor' => 1,
        ];

        $row = new EmployeeRow($init);

        $row->floor = true;
        $diff = $row->getArrayDiff();
        $this->assertEmpty($diff);

        $row->floor = false;
        $diff = $row->getArrayDiff();
        $this->assertSame(['floor' => false], $diff);
    }

    public function testIsModified_boolToNumeric()
    {
        $init = [
            'id' => 1,
            'name' => 'foo',
            'building' => 'bar',
            'floor' => true,
        ];

        $row = new EmployeeRow($init);

        $row->floor = 1;
        $diff = $row->getArrayDiff();
        $this->assertEmpty($diff);

        $row->floor = 0;
        $diff = $row->getArrayDiff();
        $this->assertSame(['floor' => 0], $diff);
    }
}
