<?php
namespace Atlas\Table;

use Atlas\Table\DataSource\Course\CourseTable;

class CompositeIdentityMapTest extends IdentityMapTest
{
    protected const TABLE_CLASS = CourseTable::class;

    protected const PRIMARY_VAL = [
        'course_subject' => 'ENGL',
        'course_number' => '100',
    ];

    protected const PRIMARY_VALS = [
        [
            'course_subject' => 'ENGL',
            'course_number' => 100,
        ],
        [
            'course_subject' => 'HIST',
            'course_number' => 100,
        ],
    ];

    protected const PRIMARY_VALS_MORE = [
        [
            'course_subject' => 'ENGL',
            'course_number' => 100,
        ],
        [
            'course_subject' => 'ENGL',
            'course_number' => 200,
        ],
        [
            'course_subject' => 'HIST',
            'course_number' => 100,
        ],
        [
            'course_subject' => 'HIST',
            'course_number' => 200,
        ],
        [
            'course_subject' => 'XXXX',
            'course_number' => 999,
        ],
    ];

    public function testGetSerial_arrayPrimaryValNotScalar()
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage("Expected scalar value for primary key 'course_subject', got array instead.");
        $this->identityMap->getSerial([
            'course_subject' => [],
        ]);
    }

    public function testGetSerial_rowPrimaryValNotScalar()
    {
        $row = $this->table->newRow();
        $row->course_subject = [];
        $this->expectException(Exception::class);
        $this->expectExceptionMessage("Expected scalar value for primary key 'course_subject', got array instead.");
        $this->identityMap->getSerial($row);
    }
}
