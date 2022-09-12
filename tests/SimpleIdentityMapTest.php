<?php
namespace Atlas\Table;

use Atlas\Testing\DataSource\Employee\EmployeeTable;

class SimpleIdentityMapTest extends IdentityMapTest
{
    protected const TABLE_CLASS = EmployeeTable::class;

    protected const PRIMARY_VAL = 1;

    protected const PRIMARY_VALS = [1, 3];

    protected const PRIMARY_VALS_MORE = [1, 2, 3, 4, 9999];

    public function testGetSerial_arrayPrimaryValNotScalar()
    {
        $this->expectException(Exception\PrimaryValueNotScalar::class);
        $this->expectExceptionMessage("Expected scalar value for primary key 'id', got array instead.");
        $this->identityMap->getSerial([]);
    }

    public function testGetSerial_rowPrimaryValNotScalar()
    {
        $row = $this->table->newRow();
        $row->id = [];
        $this->expectException(Exception\PrimaryValueNotScalar::class);
        $this->expectExceptionMessage("Expected scalar value for primary key 'id', got array instead.");
        $this->identityMap->getSerial($row);
    }
}
