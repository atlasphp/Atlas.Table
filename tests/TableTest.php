<?php
namespace Atlas\Table;

use Atlas\Statement\Bind;
use Atlas\Table\Assertions;
use Atlas\Table\DataSource\Course\CourseRow;
use Atlas\Table\DataSource\Course\CourseTable;
use Atlas\Table\DataSource\DataSourceFixture;
use Atlas\Table\DataSource\Employee\EmployeeRow;
use Atlas\Table\DataSource\Employee\EmployeeTable;
use Atlas\Table\DataSource\Nopkey\NopkeyRow;
use Atlas\Table\DataSource\Nopkey\NopkeyTable;
use Atlas\Table\Exception;
use PDO;
use PDOStatement;
use ReflectionClass;

class TableTest extends \PHPUnit\Framework\TestCase
{
    use Assertions;

    protected $table;

    protected $tableLocator;

    protected function setUp() : void
    {
        $connection = (new DataSourceFixture())->exec();

        $this->tableLocator = TableLocator::new($connection);
        $this->table = $this->tableLocator->get(EmployeeTable::CLASS);

        $rc = new ReflectionClass(Bind::CLASS);
        $rp = $rc->getProperty('instanceCount');
        $rp->setAccessible(true);
        $rp->setValue(0);
    }

    protected function logQueries()
    {
        $this->tableLocator->getConnectionLocator()->logQueries(true);
    }

    protected function getQueries()
    {
        $this->tableLocator->getConnectionLocator()->logQueries(false);
        return $this->tableLocator->getConnectionLocator()->getQueries();
    }

    public function testUpdateOnChangedPrimaryKey()
    {
        $row = $this->table->fetchRow(1);
        $row->id = 2;
        $this->expectException(Exception::CLASS);
        $this->expectExceptionMessage(
            "Primary key value for 'id' changed from '1' to 2"
        );
        $this->table->updateRow($row);
    }

    public function testFetchRow()
    {
        $expect = [
            'id' => '1',
            'name' => 'Anna',
            'building' => '1',
            'floor' => '1',
        ];

        $this->logQueries();
        $row = $this->table->fetchRow(1);
        $this->assertInstanceOf(EmployeeRow::CLASS, $row);
        $this->assertSame($expect, $row->getArrayCopy());

        // check quoting
        $queries = $this->getQueries();
        $actual = $queries[0]['statement'];
        $expect = '
            SELECT
                *
            FROM
                "employees"
            WHERE
                "id" = :_1_1_
        ';
        $this->assertSameSql($expect, $actual);

        // fetch failure
        $actual = $this->table->fetchRow(-1);
        $this->assertNull($actual);
    }

    public function testFetchRow_compositeKey()
    {
        $table = $this->tableLocator->get(CourseTable::CLASS);

        $expect = [
            'course_subject' => 'MATH',
            'course_number' => '100',
            'title' => 'Algebra',
        ];

        $this->logQueries();

        $actual = $table->fetchRow([
            'course_subject' => 'MATH',
            'course_number' => '100'
        ]);

        $this->assertSame($expect, $actual->getArrayCopy());

        // check quoting
        $queries = $this->getQueries();
        $actual = $queries[0]['statement'];
        $expect = '
            SELECT
                *
            FROM
                "courses"
            WHERE
                "course_subject" = :_1_1_ AND "course_number" = :_1_2_
        ';
        $this->assertSameSql($expect, $actual);
    }

    public function testFetchRow_compositeKey_partMissing()
    {
        $table = $this->tableLocator->get(CourseTable::CLASS);

        $this->expectException(Exception::CLASS);
        $this->expectExceptionMessage(
            "Expected scalar value for primary key 'course_number', value is missing instead."
        );

        $table->fetchRow([
            'course_subject' => 'MATH',
        ]);
    }

    public function testFetchRow_compositeKey_nonScalar()
    {
        $table = $this->tableLocator->get(CourseTable::CLASS);

        $this->expectException(Exception::CLASS);
        $this->expectExceptionMessage(
            "Expected scalar value for primary key 'course_subject', got array instead."
        );

        $table->fetchRow([
            'course_subject' => ['MATH'],
        ]);
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

        $this->logQueries();
        $actual = $this->table->fetchRows([1, 2, 3]);
        $this->assertCount(3, $actual);
        $this->assertInstanceOf(EmployeeRow::CLASS, $actual[0]);
        $this->assertInstanceOf(EmployeeRow::CLASS, $actual[1]);
        $this->assertInstanceOf(EmployeeRow::CLASS, $actual[2]);
        $this->assertSame($expect[0], $actual[0]->getArrayCopy());
        $this->assertSame($expect[1], $actual[1]->getArrayCopy());
        $this->assertSame($expect[2], $actual[2]->getArrayCopy());

        // check quoting
        $queries = $this->getQueries();
        $actual = $queries[0]['statement'];
        $expect = '
            SELECT
                *
            FROM
                "employees"
            WHERE
                "id" IN (:_1_1_, :_1_2_, :_1_3_)
        ';
        $this->assertSameSql($expect, $actual);

        // fetch failure
        $actual = $this->table->fetchRows([997, 998, 999]);
        $this->assertSame([], $actual);
    }

    public function testFetchRows_compositeKey()
    {
        $table = $this->tableLocator->get(CourseTable::CLASS);

        $expect = [
            [
                'course_subject' => 'MATH',
                'course_number' => '100',
                'title' => 'Algebra',
            ],
            [
                'course_subject' => 'ENGL',
                'course_number' => '100',
                'title' => 'Composition',
            ],
            [
                'course_subject' => 'HIST',
                'course_number' => '100',
                'title' => 'World History',
            ],
        ];

        $this->logQueries();
        $actual = $table->fetchRows($expect);
        $this->assertCount(3, $actual);
        $this->assertInstanceOf(CourseRow::CLASS, $actual[0]);
        $this->assertInstanceOf(CourseRow::CLASS, $actual[1]);
        $this->assertInstanceOf(CourseRow::CLASS, $actual[2]);
        $this->assertSame($expect[0], $actual[0]->getArrayCopy());
        $this->assertSame($expect[1], $actual[1]->getArrayCopy());
        $this->assertSame($expect[2], $actual[2]->getArrayCopy());

        // check quoting
        $queries = $this->getQueries();
        $actual = $queries[0]['statement'];
        $expect = '
            SELECT
                *
            FROM
                "courses"
            WHERE
                ("course_subject" = :_1_1_ AND "course_number" = :_1_2_)
                OR ("course_subject" = :_1_3_ AND "course_number" = :_1_4_)
                OR ("course_subject" = :_1_5_ AND "course_number" = :_1_6_)
        ';
        $this->assertSameSql($expect, $actual);
    }

    public function testInsertRow()
    {
        $row = $this->table->newRow([
            'id' => null,
            'name' => 'Mona',
            'building' => '10',
            'floor' => '99',
        ]);

        // does the insert *look* successful?
        $this->logQueries();
        $actual = $this->table->insertRow($row);
        $this->assertInstanceOf(PDOStatement::CLASS, $actual);

        // check quoting
        $queries = $this->getQueries();
        $actual = $queries[0]['statement'];
        $expect = '
            INSERT INTO "employees" (
                "name",
                "building",
                "floor"
            ) VALUES (
                :name,
                :building,
                :floor
            )
        ';
        $this->assertSameSql($expect, $actual);

        // did the autoincrement ID get retained?
        $this->assertEquals(13, $row->id);

        // was it *actually* inserted?
        $expect = [
            'id' => '13',
            'name' => 'Mona',
            'building' => '10',
            'floor' => '99',
        ];
        $actual = $this->table->getReadConnection()->fetchOne(
            'SELECT * FROM employees WHERE id = 13'
        );
        $this->assertSame($expect, $actual);

        // try to insert again, should fail on unique name
        $this->silenceErrors();
        $this->expectException(Exception::CLASS);
        $this->expectExceptionMessage(
            "Expected 1 row affected, actual 0"
        );
        $this->table->insertRow($row);
    }

    public function testUpdateRow()
    {
        // fetch a record, then modify and update it
        $row = $this->table->fetchRow(1);
        $row->name = 'Annabelle';

        // did the update *look* successful?
        $this->logQueries();
        $actual = $this->table->updateRow($row);
        $this->assertInstanceOf(PDOStatement::CLASS, $actual);

        // check quoting
        $queries = $this->getQueries();
        $actual = $queries[0]['statement'];
        $expect = '
            UPDATE "employees"
            SET
                "name" = :name
            WHERE
                id = :_2_1_
        ';
        $this->assertSameSql($expect, $actual);

        // was it *actually* updated?
        $expect = $row->getArrayCopy();
        $actual = $this->table->getReadConnection()->fetchOne(
            "SELECT * FROM employees WHERE id = 1"
        );
        $this->assertSame($expect, $actual);

        // try to update again, should be a no-op because there are no changes
        $this->assertNull($this->table->updateRow($row));

        // delete "out from under" the object ...
        $this->table->getWriteConnection()->perform(
            "DELETE FROM employees WHERE id = ?",
            [$row->id]
        );

        // then modify and try to update, should fail
        $row->name = 'Annabelle Lee';
        $this->expectException(Exception::CLASS);
        $this->expectExceptionMessage('Expected 1 row affected, actual 0.');
        $this->table->updateRow($row);
    }

    public function testDeleteRow()
    {
        // fetch a record
        $row = $this->table->fetchRow(1);

        // now delete it
        $this->logQueries();
        $actual = $this->table->deleteRow($row);
        $this->assertInstanceOf(PDOStatement::CLASS, $actual);

        // check quoting
        $queries = $this->getQueries();
        $actual = $queries[0]['statement'];
        $expect = '
            DELETE FROM "employees"
            WHERE
                id = :_2_1_
        ';
        $this->assertSameSql($expect, $actual);

        // did it delete?
        $actual = $this->table->fetchRow(1);
        $this->assertNull($actual);

        // do we still have everything else?
        $actual = $this->table->select()->columns('COUNT(*)')->fetchValue();
        $expect = 11;
        $this->assertEquals($expect, $actual);

        // try to delete the record again
        $actual = $this->table->deleteRow($row);
        $this->assertNull($actual);

        // sneaky sneaky
        $row->setLastAction($row::SELECT);
        $this->expectException(Exception::CLASS);
        $this->expectExceptionMessage(
            "Expected 1 row affected, actual 0"
        );
        $this->table->deleteRow($row);
    }

    protected function silenceErrors()
    {
        $conn = $this->table->getWriteConnection();
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);
    }

    public function testUpdateRow_noPrimaryKey()
    {
        $table = $this->tableLocator->get(NopkeyTable::CLASS);
        $row = $table->newRow([
            'name' => 'Bolivar Shagnasty',
            'email' => 'boshag@example.com',
        ]);
        $table->insertRow($row);

        $row->email = 'boshag@example.org';
        $this->expectException(Exception::CLASS);
        $this->expectExceptionMessage("Cannot update row on table 'nopkeys' without primary key.");

        $table->updateRow($row);
    }

    public function testDeleteRow_noPrimaryKey()
    {
        $table = $this->tableLocator->get(NopkeyTable::CLASS);
        $row = $table->newRow([
            'name' => 'Bolivar Shagnasty',
            'email' => 'boshag@example.com',
        ]);
        $table->insertRow($row);

        $row->email = 'boshag@example.org';
        $this->expectException(Exception::CLASS);
        $this->expectExceptionMessage("Cannot delete row on table 'nopkeys' without primary key.");

        $table->deleteRow($row);
    }
}
