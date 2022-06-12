<?php
/**
 * This file was generated by Atlas. Changes will be overwritten.
 */
declare(strict_types=1);

namespace Atlas\Table\DataSource\Employee\_generated;

use Atlas\Table\Table;
use Atlas\Table\DataSource\Employee\EmployeeRow;
use Atlas\Table\DataSource\Employee\EmployeeTableSelect;

/**
 * @method EmployeeRow|null fetchRow($primaryVal)
 * @method EmployeeRow[] fetchRows(array $primaryVals)
 * @method EmployeeTableSelect select(array $whereEquals = [])
 * @method EmployeeRow newRow(array $cols = [])
 * @method EmployeeRow newSelectedRow(array $cols)
 */
abstract class EmployeeTable_ extends Table
{
    public const DRIVER = 'sqlite';

    public const NAME = 'employees';

    public const COLUMNS = [
        'id' => [
            'name' => 'id',
            'type' => 'INTEGER',
            'size' => null,
            'scale' => null,
            'notnull' => false,
            'default' => null,
            'autoinc' => true,
            'primary' => true,
            'options' => null,
        ],
        'name' => [
            'name' => 'name',
            'type' => 'VARCHAR',
            'size' => 10,
            'scale' => null,
            'notnull' => true,
            'default' => null,
            'autoinc' => false,
            'primary' => false,
            'options' => null,
        ],
        'building' => [
            'name' => 'building',
            'type' => 'INTEGER',
            'size' => null,
            'scale' => null,
            'notnull' => false,
            'default' => null,
            'autoinc' => false,
            'primary' => false,
            'options' => null,
        ],
        'floor' => [
            'name' => 'floor',
            'type' => 'INTEGER',
            'size' => null,
            'scale' => null,
            'notnull' => false,
            'default' => null,
            'autoinc' => false,
            'primary' => false,
            'options' => null,
        ],
    ];

    public const COLUMN_NAMES = [
        'id',
        'name',
        'building',
        'floor',
    ];

    public const COLUMN_DEFAULTS = [
        'id' => null,
        'name' => null,
        'building' => null,
        'floor' => null,
    ];

    public const PRIMARY_KEY = [
        'id',
    ];

    public const COMPOSITE_KEY = false;

    public const AUTOINC_COLUMN = 'id';

    public const AUTOINC_SEQUENCE = null;

    public const ROW_CLASS = EmployeeRow::CLASS;
}
