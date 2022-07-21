<?php
namespace Atlas\Table\IdentityMap;

use Atlas\Table\DataSource\Employee\EmployeeTable;
use Atlas\Table\Exception;
use Atlas\Table\IdentityMapTest;

class SimpleIdentityMapTest extends IdentityMapTest
{
    protected const TABLE_CLASS = EmployeeTable::class;

    protected const PRIMARY_VAL = 1;

    protected const PRIMARY_VALS = [1, 3];

    protected const PRIMARY_VALS_MORE = [1, 2, 3, 4, 9999];

    protected function newIdentityMap()
    {
        return new SimpleIdentityMap($this->table);
    }

    public function testGetSerial_arrayPrimaryValNotScalar()
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage("Expected scalar value for primary key 'id', got array instead.");
        $this->identityMap->getSerial([]);
    }

    public function testGetSerial_rowPrimaryValNotScalar()
    {
        $row = $this->table->newRow();
        $row->id = [];
        $this->expectException(Exception::class);
        $this->expectExceptionMessage("Expected scalar value for primary key 'id', got array instead.");
        $this->identityMap->getSerial($row);
    }
}
