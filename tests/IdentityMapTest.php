<?php
namespace Atlas\Table;

use Atlas\Table\DataSource\Course\CourseRow;
use Atlas\Table\DataSource\Course\CourseTable;
use Atlas\Table\DataSource\DataSourceFixture;
use Atlas\Table\DataSource\Employee\EmployeeRow;
use Atlas\Table\DataSource\Employee\EmployeeTable;
use Atlas\Table\DataSource\Nopkey\NopkeyRow;
use Atlas\Table\DataSource\Nopkey\NopkeyTable;
use Atlas\Table\Exception;

/**
 * make abstract, extend with SimpleIdentityMapTest and CompositeIdentityMapTest
 */
abstract class IdentityMapTest extends \PHPUnit\Framework\TestCase
{
    protected const TABLE_CLASS = null;

    protected const PRIMARY_VAL = null;

    protected const PRIMARY_VALS = [];

    protected const PRIMARY_VALS_MORE = [];

    protected $table;

    protected $tableLocator;

    protected $identityMap;

    protected function setUp() : void
    {
        $connection = (new DataSourceFixture())->exec();
        $this->tableLocator = TableLocator::new($connection);
        $this->table = $this->tableLocator->get(static::TABLE_CLASS);
        $this->identityMap = $this->newIdentityMap();
    }

    abstract protected function newIdentityMap();

    public function testSetRow()
    {
        $row = $this->table->fetchRow(static::PRIMARY_VAL);
        $this->identityMap->setRow($row);
        $rowAgain = $this->table->fetchRow(static::PRIMARY_VAL);
        $serial = $this->identityMap->getSerial($row);
        $rowClass = get_class($row);

        $this->expectException(Exception::class);
        $this->expectExceptionMessage(
            "{$rowClass} with serial {$serial} already exists in IdentityMap."
        );
        $this->identityMap->setRow($rowAgain);
    }

    public function testMemRow()
    {
        // memoize a selected row
        $row = $this->table->fetchRow(static::PRIMARY_VAL);
        $mem = $this->identityMap->memRow($row);
        $this->assertSame($row, $mem);

        // select the row again, should *not* be the same as the memoized row
        $rowAgain = $this->table->fetchRow(static::PRIMARY_VAL);
        $memAgain = $this->identityMap->memRow($rowAgain);
        $this->assertNotSame($rowAgain, $memAgain);
        $this->assertSame($mem, $memAgain);
    }

    public function testFetchRow()
    {
        $row = $this->identityMap->fetchRow(static::PRIMARY_VAL, $this->table->select());
        $rowAgain = $this->identityMap->fetchRow(static::PRIMARY_VAL);
        $this->assertSame($row, $rowAgain);
    }

    public function testFetchRows()
    {
        $rows = $this->identityMap->fetchRows(static::PRIMARY_VALS);
        $this->assertCount(2, $rows);
        // $this->assertSame('1', $rows[0]->id);
        // $this->assertSame('3', $rows[1]->id);

        $again = $this->identityMap->fetchRows(static::PRIMARY_VALS);
        $this->assertSame($rows, $again);

        $more = $this->identityMap->fetchRows(static::PRIMARY_VALS_MORE);
        $this->assertCount(4, $more);
        // $this->assertSame('1', $more[0]->id);
        // $this->assertSame('2', $more[1]->id);
        // $this->assertSame('3', $more[2]->id);
        // $this->assertSame('4', $more[3]->id);
        $this->assertSame($rows[0], $more[0]); // id 1 should be memorized
        $this->assertSame($rows[1], $more[2]); // id 3 should be memorized
    }

    abstract public function testGetSerial_arrayPrimaryValNotScalar();

    abstract public function testGetSerial_rowPrimaryValNotScalar();
}
